<?php

namespace Ruanzhu\Generators;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use \PhpOffice\PhpWord\SimpleType\Jc;
use Illuminate\Support\Str;

class RuanzhuManual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruanzhu:manual
        {-t|--title : 软件名称+版本号，默认为软件著作权程序鉴别材料生成器V1.0，此名称用于生成页眉}
        {--path=tests/Browser/screenshots : 截图保存路径}
        {--output=manual.docx : 保存文件名}
    ';

    protected $texts = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate manual for software copyright.';

    protected $settings = [
        'font-size' => '10.5',
        'font-name' => '宋体',
        'line-spacing' => '1.0',
    ];

    protected $model_actions = [
        'index',
        'create',
        'view',
        'edit',
        // 'delete',
    ];


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $title = $this->option('title');
        if (!$title) {
            $title = config('app.name');
        }

        $this->texts = config('ruanzhu');

        // prepare doc
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName($this->settings['font-name']);
        $phpWord->setDefaultFontSize($this->settings['font-size']);
        $phpWord->setDefaultParagraphStyle($this->settings);

        $section = $phpWord->addSection($this->settings);

        // 页眉标注软著名称及版本号,并在右上角标注页码
        $header = $section->addHeader();
        $header->addPreserveText($title, null, ['alignment' => Jc::CENTER]);
        $header->addPreserveText('{PAGE}/{NUMPAGES}', null, ['alignment' => Jc::RIGHT]);

        $path = base_path($this->option('path'));
        $output = base_path($this->option('output'));

        // models
        $schemaPath = resource_path('model_schemas/');
        $schemas = scandir($schemaPath);
        $models = [];
        foreach($schemas as $filename) {
            if (Str::startsWith($filename, '.')) {
                continue;
            }

            $models[] = Str::snake(Str::plural(rtrim($filename,'.json')));
        }

        // browse
        $rand = rand(0, count($this->texts['manual']['browse'])-1);
        $section->addText($this->texts['manual']['browse'][$rand]);
        $filename = 'Browse-browse.png';
        $fullpath = realpath($path.DIRECTORY_SEPARATOR.$filename);
        if ( file_exists($fullpath) ){
            list($width, $height) = getimagesize($fullpath);
            $rate = 0.3;
            $settings = [
                'alignment' => Jc::CENTER,
                'height' => $height * $rate,
                'width' => $width * $rate,
            ];
            $section->addImage($fullpath, $settings);
            $section->addTextBreak();
        }

        // login
        $rand = rand(0, count($this->texts['manual']['login'])-1);
        $text = $this->texts['manual']['login'][$rand];
        $modules = array_map(function($item){
            return __('models/'.$item.'.plural');
        }, $models);
        $text = str_replace('{$modules}', implode('、',$modules), $text);

        $section->addText($text);
        $filename = 'Login-login.png';
        $fullpath = realpath($path.DIRECTORY_SEPARATOR.$filename);
        list($width, $height) = getimagesize($fullpath);
        $rate = 0.3;
        $settings = [
            'alignment' => Jc::CENTER,
            'height' => $height * $rate,
            'width' => $width * $rate,
        ];
        $section->addImage($fullpath, $settings);
        $section->addTextBreak();

        // dashboard
        $rand = rand(0, count($this->texts['manual']['dashboard'])-1);
        $section->addText($this->texts['manual']['dashboard'][$rand]);
        $filename = 'Dashboard-dashboard.png';
        $fullpath = realpath($path.DIRECTORY_SEPARATOR.$filename);
        list($width, $height) = getimagesize($fullpath);
        $rate = 0.3;
        $settings = [
            'alignment' => Jc::CENTER,
            'height' => $height * $rate,
            'width' => $width * $rate,
        ];
        $section->addImage($fullpath, $settings);
        $section->addTextBreak();

        foreach ($models as $model) {
            $model_name = Str::studly(Str::singular($model));

            foreach ($this->model_actions as $action) {
                $filename = $model_name.'-'.$action.'.png';
                $fullpath = $path.DIRECTORY_SEPARATOR.$filename;
                if (!file_exists($fullpath)){
                    $this->warn('File '.$fullpath.' doesn\'t exists. Skipping');
                    break;
                }
                list($width, $height) = getimagesize($fullpath);
                $rate = 0.3;
                $settings = [
                    'alignment' => Jc::CENTER,
                    'height' => $height * $rate,
                    'width' => $width * $rate,
                ];
                $key = 'list-'.$action;
                $rand = rand(0,count($this->texts['manual'][$key])-1);
                $text = str_replace('{$module_name}', __('models/'.$model.'.plural'), $this->texts['manual'][$key][$rand]);
                $section->addText($text);
                $section->addImage($fullpath, $settings);
                $section->addTextBreak();
            }
        }

        $objWriter = IOFactory::createWriter($phpWord);
        $objWriter->save($output);

        $this->info('Done generating ruanzhu manual.');

        return true;
    }
}