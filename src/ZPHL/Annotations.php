<?php


/**
 * Created by PhpStorm.
 * User: zibi
 * Date: 2019-04-26
 * Time: 10:45
 */

namespace ZPHL;

class Annotations
{
    public function __construct($className)
    {
        $rc = new \ReflectionClass($className);

        foreach ($rc->getProperties() as $property)
        {
            $doc = explode("\n",$property->getDocComment());

            if(count($doc) == 1)
            {
                $line = explode(' ',$doc[0]);
                array_shift($line);
                array_pop($line);
                $doc[0] = join(' ',$line);
            }

            $doc = array_filter($doc,
                function($item)
                {
                    return str_contains($item,'@');
                }
            );
            foreach($doc as $line)
            {
                $line = explode('@',$line);
                array_shift($line);
                $line = join('@',$line);

                $line = explode(' ',$line);
                $annoName = array_shift($line);
                $annoContent = join(' ',$line);

                $this->{$property->getName()}[$annoName] = $annoContent;
            }
        }

        return $this;
    }
}