<?php

/**
 * User: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 * Date: 28.08.17 16:56
 *
 * http://domexception.blogspot.com/2013/08/php-magic-methods-and-arrayobject.html
 *
 */

class StrTest
{
    private $string;

    private $functionList = [
        'addcslashes'=>['foo[ ]','A..z'],
        'addslashes'=>["Is your name O'Reilly?"],
        'bin2hex'=>["11111001"],
        'chunk_split'=>['ABCDEFGHIJKLMNOPQRTUVWXYZABCDEFGHIJKLMNOPQRTUVWXYZABCDEFGHIJKLMNOPQRTUVWXYZABCDEFGHIJKLMNOPQRTUVWXYZABCDEFGHIJKLMNOPQRTUVWXYZ'],
        'convert_uudecode'=>['+22!L;W9E(%!(4\"$`\n`'],
        'convert_uuencode'=>["test\ntext text\r\n"],
        'count_chars'=>["Two Ts and one F."],
        'crc32'=>["The quick brown fox jumped over the lazy dog."],
        'crypt'=>["The quick brown fox jumped over the lazy dog.",0],
        'explode'=>[' ',"The quick brown fox jumped over the lazy dog."],
        'hex2bin'=>["6578616d706c65206865782064617461"],
        'html_entity_decode'=>["I'll \"walk\" the <b>dog</b> now"],
        'htmlentities'=>["I'll \"walk\" the <b>dog</b> now"],
        'htmlspecialchars_decode'=>["I'll \"walk\" the <b>dog</b> now"],
        'htmlspecialchars'=>["I'll \"walk\" the <b>dog</b> now"],
        'lcfirst'=>["Hello World"],
        'levenshtein'=>["Hello World","AAAAAA"],
        'ltrim'=>["    Hello World"," "],
        'md5'=>["Hello World"],
        'metaphone'=>["Hello World"],
        'money_format'=>['%i',1234.56],
        'nl2br'=>["The quick brown\n fox jumped over the lazy dog."],
        'ord'=>["The quick brown\n fox jumped over the lazy dog."],
        'quoted_printable_decode'=>["The quick brown\n fox jumped over the lazy dog."],
        'quoted_printable_encode'=>["The quick brown\n fox jumped over the lazy dog."],
        'quotemeta'=>["The \" ' [ * quick brown\n fox jumped over the lazy dog."],
        'rtrim'=>["Hello world!!!!",'!'],
        'sha1'=>["Hello world!!!!",'!'],
        'similar_text'=>["Hello world!!!!","Hello world!!"],
        'sprintf'=>["%s,%d","Hello",10],
        'sscanf'=>["SN/2350001", "SN/%d"],
        'str_getcsv'=>["a,b,c"],
        'str_ireplace'=>["HELLO","Hello","HELLO world!"],
        'str_replace'=>["HELLO","Hello","HELLO world!"],
        'str_pad'=>["Hello world",20,'!'],
        'str_repeat'=>["Hello world!",10],
        'str_rot13'=>["Hello world!"],
//        'str_shuffle'=>["Hello world!"], // cannot test
        'str_split'=>["Hello world!",2],
        'str_word_count'=>["Hello world!"],
        'strcasecmp'=>["Hello world!","HeLLo World!"],
        'strchr'=>["Hello world!","lo"],
        'strcmp'=>["Hello world!","Hello world!"],
        'strcoll'=>["Hello world!","Hello world!"],
        'strcspn'=>["abcdhelloabcd","abcd"],
        'strip_tags'=>["Hello<br />world!"],
        'stripcslashes'=>["Hello\'world!"],
        'stripos'=>["Hello world!",'World'],
        'stripslashes'=>["Hello \' world!"],
        'stristr'=>["Hello world!",'Wor'],
        'strlen'=>["Hello world!"],
        'strnatcasecmp'=>["Hello world!","Hello"],
        'strnatcmp'=>["Hello world!","Hello"],
        'strncasecmp'=>["Hello world!","Hello",5],
        'strncmp'=>["Hello world!","Hello",5],
        'strpbrk'=>["Hello world!","wo"],
        'strpos'=>["Hello world!","wor"],
        'strrchr'=>["Hello world!","w"],
        'strrev'=>["Hello world!"],
        'strripos'=>["Hello world!",'Wor'],
        'strrpos'=>["Hello world!",'wor'],
        'strspn'=>["Hello world!",'wor'],
        'strstr'=>["Hello world!",'wor'],
        'strtok'=>["Hello world!",' '],
        'strtolower'=>["Hello world!"],
        'strtoupper'=>["Hello world!"],
        'strtr'=>["Hello world!",'eo','EO'],
        'substr_compare'=>["abcde", "bc", 1, 3],
        'substr_count'=>["abcde abcde abcde abcde", "bc"],
        'substr_replace'=>["abcde", "ABC",0,3],
        'substr'=>["abcde", 0,3],
        'trim'=>["  Hello world! "],
        'ucfirst'=>["hello world!"],
        'ucwords'=>["hello world!"],
        'wordwrap'=>["hello world!",4,'<br />'],
    ];

    public function run()
    {
        $this->runTestsFromFunctionList();
    }
    public function runTestsFromFunctionList()
    {
        foreach($this->functionList as $functionName=>$params)
        {
            echo "{$functionName} :: ";
            echo
            $this->runTestByFunctionName($functionName,$params)
                ?'OK':'ERROR'
            ;
            echo PHP_EOL;
            ;
        }
    }
    public function runTestMethods()
    {
        foreach(get_class_methods(self::class) as $methodName)
        {
            if(str_beginswith($methodName,'test'))
            {
                echo "{$methodName} :: ";
                echo
                    $this->$methodName()
                    ?'OK':'ERROR'
                ;
                echo PHP_EOL;
            }
        }
    }

    public function runTestByFunctionName($functionName,$paramsIn)
    {
        $params = $paramsIn;

        $found = false;

        $ref=new \ReflectionFunction($functionName);
        foreach($ref->getParameters() as $key=>$param)
        {

            if(
                $param->name == 'str' // explode
                || $param->name == 'subject' // str_replace
            )
            {
                $found = $key;
            }
        }

        if($found===false || $functionName=='substr_compare')
        {
            $in = array_shift($params);
        }
        else
        {
            $in = current(array_splice($params,$found,1));
        }

        $str = new Str($in);

        $ori = call_user_func_array($functionName,$paramsIn);
        $ret = call_user_func_array([$str,$functionName],$params);

        return $ori == $ret;
    }

}