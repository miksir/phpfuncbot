<?php


namespace phpfuncbot\Logger;


class FileLogger extends AbstractLogger
{
    /**
     * @var string
     */
    private $filename;
    private $fh;

    public function __construct(string $currentDir, string $filename)
    {
        if ($filename[0] !== '/') {
            $filename = $currentDir . '/' . $filename;
        }

        $this->filename = $filename;
        $this->openFile($filename);
        if ($this->fh === false) {
            throw new \InvalidArgumentException("Can't open file {$filename} for writing");
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->isPassMessage($level)) {
            $this->writeLine("[" . date("Y-m-d H:i:s") . "] \"{$level}\" {$message}\n");
        }
    }

    private function writeLine(string $line)
    {
        $wrnt = false;
        if ($this->fh !== false) {
            $wrnt = fwrite($this->fh, $line);
        }
        if ($wrnt === false) {
            if ($this->fh !== false) {
                fclose($this->fh);
            }
            $this->openFile($this->filename);
            if ($this->fh !== false) {
                fwrite($this->fh, $line);
            }
        }
    }

    private function openFile(string $filename)
    {
        $fh = fopen($filename, 'a');
        $this->fh = $fh;
    }
}