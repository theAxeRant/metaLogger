<?php

namespace Theaxerant\Metalogger\Util\validator;

class Validator extends \Valitron\Validator {

    /**
     * Setup validation
     *
     * @param array  $data
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($data = []) {

        $this->_fields = $data;

        // set lang in the follow order: constructor param, static::$_lang, default to en
        $lang = static::lang();

        // set langDir in the follow order: constructor param, static::$_langDir, default to package lang dir
        $langDir = static::langDir();

        // Load language file in directory
        $langFile = rtrim($langDir, '/') . '/' . $lang . '.php';

        // The Default validation on determining if a language file exists does not work when in a phar:// context
        $langMessages = include $langFile;
        static::$_ruleMessages = array_merge(static::$_ruleMessages, $langMessages);

    }
}
