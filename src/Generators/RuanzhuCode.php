<?php

namespace Ruanzhu\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class RuanzhuCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruanzhu:code
        {--t|--title : 软件名称+版本号，默认为项目名称，此名称用于生成页眉}
        {--i|--indirs=app/,resources/views/,vendor/ : 源码所在文件夹，可以指定多个，默认为当前目录}
        {--e|--exts=php : 源代码后缀，可以指定多个，默认为PHP源代码}
        {--font-name=宋体 : 字体，默认为宋体}
        {--font-size=10.5 : 字号，默认为五号，即10.5号}
        {--line-spacing=1.0 : 行距，默认为固定值10.5}
        {--line-numbering : 是否显示行号}
        {--x|--excludes : 需要排除的文件或路径，可以指定多个}
        {--o|--outfile=code.docx : 输出文件（docx格式），默认为当前目录的code.docx}
        {--f|--force : 删除已存在的文件}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate doc for software copyright.';

    protected $extensions = [
        'php',
    ];

    protected $paths = [
        'app/',
        'resources/views/',
        'vendor/',
    ];

    protected $excludes = [
        'vendor/composer/',
        'vendor/autoload.php',
    ];

    protected $settings = [
        'font-size' => '10.5',
        'font-name' => '宋体',
        'line-spacing' => '1.0',
        'line-numbering' => false,
    ];

    protected $title = '';

    protected $outfile = 'code.docx';

    protected $maxLines = 5000;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->title = $this->option('title')) {
            $this->title = config('app.name');
        }
        $this->comment('开始生成软著代码文档 '.$this->title);

        if ($this->option('indirs')) {
            $this->paths = explode(',',$this->option('indirs'));
        }

        if ($this->option('exts')) {
            $this->extensions = explode(',', $this->option('exts'));
        }
        if ($this->option('excludes')) {
            $this->excludes = explode(',', $this->option('excludes'));
        }

        if ($this->option('font-size')<1.0) {
            $this->error('字体大小不能小于1');
            return;
        }

        if ($this->option('line-spacing')<0.0) {
            $this->error('行距大小不能小于0');
            return;
        }

        if ($this->hasOption('line-numbering')) {
            $this->settings['line-numbering'] = $this->option('line-numbering');
        }

        $this->settings['font-name'] = $this->option('font-name');
        $this->settings['font-size'] = $this->option('font-size');
        $this->settings['line-spacing'] = $this->option('line-spacing');


        $outfilename = $this->option('outfile');
        $outfile = base_path($outfilename);
        if (file_exists($outfile)){
            if (!$this->option('force'))
            {
                $this->error($outfilename.'已存在。');
                return false;
            }
            unlink($outfile);
        }

        $this->outfile = $outfile;

        foreach ($this->excludes as &$exclude) {
            $exclude = base_path($exclude);
        }

        $this->generateDoc();

        $this->info('软著代码文档生成结束');
    }

    public function generateDoc()
    {
        // prepare doc
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName($this->settings['font-name']);
        $phpWord->setDefaultFontSize($this->settings['font-size']);
        $settings = [
            'lineHeight' => $this->settings['line-spacing'],
        ];

        if ($this->settings['line-numbering']) {
            $settings['lineNumbering'] = ['restart' => 'continuous'];
        }
        $phpWord->setDefaultParagraphStyle($settings);

        $section = $phpWord->addSection($settings);

        // 第一步，查找代码文件
        $files = $this->findCode($this->paths, $this->excludes, $this->extensions);
        shuffle($files);

        $totalLines = 0;
        // 页眉标注软著名称及版本号,并在右上角标注页码
        $header = $section->addHeader();
        $header->addPreserveText($this->title, null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $header->addPreserveText('{PAGE}/{NUMPAGES}', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);

        // 第二步，逐个把代码文件写入到docx中
        foreach ($files as $file) {
            if ($this->matchExclude($file)) {
                continue;
            }

            if ($totalLines > $this->maxLines) {
                break;
            }
            $fileContent = file_get_contents($file);
            $fileContent = preg_replace('~//?\s*\*[\s\S]*?\*\s*//?~', '', $fileContent);
            $fileContent = preg_replace('~/\*.*?\*/|//.*?\n~', '', $fileContent);
            $fileContent = preg_replace('~\n\ *\{\n\ *\}\ *\n~', "{}\n", $fileContent);
            $fileContent = preg_replace('~\[\n\ *\];\ *\n~', "[];\n", $fileContent);
            $fileContent = preg_replace('~\(\n\ *\)\ *\n~', "()\n", $fileContent);
            $fileContent = preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $fileContent);
            $lines = 0;
            $content = preg_replace('/(\r\n|\r|\n)/', '</w:t><w:br/><w:t>', htmlspecialchars($fileContent), -1, $lines);
            $totalLines += $lines;
            $section->addText($content, $this->settings);
            $section->addTextBreak();
        }

        $objWriter = IOFactory::createWriter($phpWord);
        $objWriter->save($this->outfile);

        return true;
    }

    protected function findCode($paths, $excludes, $exts)
    {
        $files = [];
        foreach ($paths as $path) {
            $path = base_path($path);
            $files = array_merge($files, $this->scanFiles($path, $excludes, $exts));
        }
        return $files;
    }

    protected function matchExclude($path)
    {
        foreach($this->excludes as $ex){
            if (Str::startsWith($path, $ex)){
                return true;
            }
        }
        return false;
    }

    protected function matchExtensions($path, $exts)
    {
        foreach ($exts as $ext) {
            if (!Str::endsWith($path, $ext)) {
                return false;
            }
        }
        return true;
    }

    protected function scanFiles($path, $excludes, $exts): array
    {
        $matchedFiles = [];
        $files = scandir($path);
        foreach ($files as $file) {
            $fullpath = realpath($path.DIRECTORY_SEPARATOR.$file);
            //忽略隐藏文件以及相对目录
            if (Str::startsWith($file,'.')) {
                continue;
            }

            if (isset($excludes[$fullpath])) {
                continue;
            }

            //判断是否是文件夹
            if (is_dir($fullpath)) {
                $f = $this->scanFiles($fullpath, $excludes, $exts);
                $matchedFiles = array_merge($matchedFiles, $f);
            }
            else if (!$this->matchExtensions($file, $exts)) {
                continue;
            }
            else {
                $matchedFiles[] = $fullpath;
            }
        }

        return $matchedFiles;
    }
}