<?php



namespace FJ;



abstract class CLI
{
    private   $argc;
    private   $argv;
    protected $opts      = [];
    protected $remaining = [];



    /**
     * This provides a list of the short options, in class getopts format.
     *
     * @return string
     */
    protected function getShortOpts () { return ""; }

    /**
     * This provides an array of the long options.
     *
     * @return array
     */
    protected function getLongOpts () { return []; }

    /**
     * CLI entry point.
     *
     * @return int
     */
    abstract public function main ();



    protected function argc () { return $this->argc; }
    protected function argv () { return $this->argv; }



    public function __construct ()
    {
        $this->argc = $_SERVER['argc'];
        $this->argv = $_SERVER['argv'];

        clog($this->argc, $this->argv);

        $shortOpts = $this->getShortOpts();
        $longOpts  = $this->getLongOpts();

        clog("short-opts", $shortOpts);
        clog(" long-opts", $longOpts);

        $optind     = 0;
        $this->opts = getopt($shortOpts, $longOpts, $optind);

        clog("options", $this->opts);

        $this->remaining = array_slice($this->argv, $optind);
        clog($optind, $this->remaining);
    }



    protected function getopt ( $opt ) { return array_key_exists($opt, $this->opts) ? $this->opts[$opt] : false; }
    protected function hasopt ( $opt ) { return array_key_exists($opt, $this->opts); }



    public static function cleanInput ( $string )
    {
        $string = strtolower($string);
        $string = preg_replace("/[^a-z0-9\-]/", "", $string);
        return $string;
    }



    public static function run ()
    {
        $class = get_called_class();
        // clog("get_called_class", $class);

        /** @var CLI $cli */
        $cli = new $class();
        $cli->main();
    }
}
