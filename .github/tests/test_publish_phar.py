import hashlib
import os
from pathlib import Path
import shlex
import subprocess
import tempfile
import unittest


PUBLISH_SCRIPT = Path(__file__).resolve().parents[1] / 'scripts/publish-phar.sh'


class PublishPharTest(unittest.TestCase):
    branch = 'feature/phar'

    def git(self, *arguments, cwd=None):
        return subprocess.check_output(
            ['git', *arguments], cwd=cwd or self.checkout,
            stderr=subprocess.PIPE, text=True,
        ).strip()

    def configure(self, checkout):
        for key, value in [
            ('user.name', 'PHAR test'),
            ('user.email', 'phar-test@example.invalid'),
            ('commit.gpgsign', 'false'),
            ('core.hooksPath', '.git/hooks'),
        ]:
            self.git('config', key, value, cwd=checkout)

    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory(prefix='metalogger-publish-')
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        self.remote = self.root / 'remote.git'
        self.checkout = self.root / 'checkout'
        self.git('init', '--bare', str(self.remote), cwd=self.root)
        self.git('init', '-b', self.branch, str(self.checkout), cwd=self.root)
        self.configure(self.checkout)
        (self.checkout / 'release').mkdir()
        phar = self.checkout / 'release/metaLogger.phar'
        phar.write_bytes(b'old phar')
        phar.chmod(0o755)
        (self.checkout / 'source.php').write_text('<?php // source\n')
        self.git('add', '.')
        self.git('commit', '-qm', 'Initial source')
        self.source = self.git('rev-parse', 'HEAD')
        self.git('remote', 'add', 'origin', str(self.remote))
        self.git('push', '-u', 'origin', 'HEAD')
        self.git('checkout', '--detach', self.source)
        self.artifact = self.root / 'incoming.phar'
        self.artifact.write_bytes(b'verified new phar')

    def remote_head(self):
        return self.git('--git-dir=' + str(self.remote), 'rev-parse', 'refs/heads/' + self.branch)

    def publish(self, **overrides):
        env = dict(os.environ,
                   TARGET_BRANCH=self.branch, SOURCE_SHA=self.source,
                   PHAR_PATH=str(self.artifact), PHAR_VERSION='1.1.0',
                   PHAR_SHA256=hashlib.sha256(self.artifact.read_bytes()).hexdigest())
        env.update(overrides)
        return subprocess.run(['bash', str(PUBLISH_SCRIPT)], cwd=self.checkout,
                              env=env, text=True, capture_output=True)

    def other_commit(self):
        writer = self.root / 'other writer'
        self.git('clone', '--branch', self.branch, str(self.remote), str(writer), cwd=self.root)
        self.configure(writer)
        (writer / 'source.php').write_text('<?php // newer source\n')
        self.git('add', 'source.php', cwd=writer)
        self.git('commit', '-qm', 'New source', cwd=writer)
        return writer, self.git('rev-parse', 'HEAD', cwd=writer)

    def install_push_hook(self, content):
        hook = self.checkout / '.git/hooks/pre-push'
        hook.write_text('#!/bin/sh\n' + content + '\n')
        hook.chmod(0o755)

    def test_publishes_only_the_phar_with_declared_version(self):
        result = self.publish()
        self.assertEqual(0, result.returncode, result.stderr)
        head = self.remote_head()
        self.assertNotEqual(self.source, head)
        self.assertEqual('release/metaLogger.phar', self.git('diff-tree', '--no-commit-id', '--name-only', '-r', head))
        self.assertEqual('verified new phar', self.git('show', head + ':release/metaLogger.phar'))
        self.assertEqual('Build PHAR 1.1.0 from ' + self.source[:12], self.git('log', '-1', '--format=%s'))
        self.assertTrue(self.git('ls-tree', head, 'release/metaLogger.phar').startswith('100755 '))

    def test_unchanged_phar_does_not_create_a_commit(self):
        self.artifact.write_bytes(b'old phar')
        result = self.publish()
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertEqual(self.source, self.remote_head())

    def test_invalid_checksum_prevents_publication(self):
        result = self.publish(PHAR_SHA256='0' * 64)
        self.assertNotEqual(0, result.returncode)
        self.assertEqual(self.source, self.remote_head())
        self.assertEqual(b'old phar', (self.checkout / 'release/metaLogger.phar').read_bytes())

    def test_stale_source_is_skipped(self):
        writer, newer = self.other_commit()
        self.git('push', 'origin', 'HEAD', cwd=writer)
        result = self.publish()
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn('stale PHAR', result.stdout)
        self.assertEqual(newer, self.remote_head())
        self.assertEqual(self.source, self.git('rev-parse', 'HEAD'))

    def test_concurrent_push_is_not_overwritten_or_rebased(self):
        writer, newer = self.other_commit()
        self.install_push_hook('git -C ' + shlex.quote(str(writer)) + ' push origin HEAD:refs/heads/' + self.branch)
        result = self.publish()
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn('advanced during publication', result.stdout)
        self.assertEqual(newer, self.remote_head())
        self.assertEqual('old phar', self.git('--git-dir=' + str(self.remote), 'show', newer + ':release/metaLogger.phar'))

    def test_failed_push_is_reported_when_source_has_not_advanced(self):
        self.install_push_hook('exit 1')
        result = self.publish()
        self.assertNotEqual(0, result.returncode)
        self.assertEqual(self.source, self.remote_head())

    def test_wrong_checkout_is_rejected(self):
        result = self.publish(SOURCE_SHA='0' * 40)
        self.assertNotEqual(0, result.returncode)
        self.assertEqual(self.source, self.remote_head())

    def test_unrelated_staged_changes_are_rejected(self):
        (self.checkout / 'source.php').write_text('<?php // unrelated\n')
        self.git('add', 'source.php')
        result = self.publish()
        self.assertNotEqual(0, result.returncode)
        self.assertIn('clean checkout', result.stderr)
        self.assertEqual(self.source, self.remote_head())

    def test_missing_source_branch_fails_without_committing(self):
        self.git('--git-dir=' + str(self.remote), 'update-ref', '-d', 'refs/heads/' + self.branch)
        result = self.publish()
        self.assertNotEqual(0, result.returncode)
        self.assertEqual(self.source, self.git('rev-parse', 'HEAD'))

    def test_invalid_branch_is_rejected(self):
        result = self.publish(TARGET_BRANCH='invalid..branch')
        self.assertNotEqual(0, result.returncode)
        self.assertEqual(self.source, self.remote_head())


if __name__ == '__main__':
    unittest.main()
