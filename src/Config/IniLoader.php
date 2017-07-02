<?php


namespace phpfuncbot\Config;


class IniLoader implements Loader
{
    /**
     * @var string
     */
    private $inifile;
    /**
     * @var array;
     */
    private $iniarray;

    public function __construct(string $inifile)
    {
        $this->inifile = $inifile;
        $this->iniarray = parse_ini_file($inifile, true);
    }

    /**
     * @param string $section
     * @return array
     * @throws ConfigException
     */
    public function getArray(string $section): array
    {
         if (!isset($this->iniarray[$section])) {
             throw new ConfigException("Ini section [{$section}] not found");
         }
         return $this->iniarray[$section];
    }
}