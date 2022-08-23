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
        {-t|--title=软件著作权程序鉴别材料生成器V1.0 : 软件名称+版本号，默认为软件著作权程序鉴别材料生成器V1.0，此名称用于生成页眉}
        {--path=tests/Browser/screenshots : 截图保存路径}
        {--output=manual.docx : 保存文件名}
    ';

    protected $texts = [
        'browse' => [
            '打开浏览器，在浏览器输入网址后打开的界面如下：',
            '双击桌面上的快捷方式打开系统登录：'
        ],
        'login' => [
            '用户输入正确的用户名和密码后，页面会跳转到仪表板界面。用户可以在此进行{$modules}等操作。',
            '输入正确的用户名和密码后，页面会发生跳转，会打开仪表盘。在这里用户可以对{$modules}进行增删改查等操作。',
        ],
        'dashboard' => [
            '登录后，页面会跳转到仪表盘界面。左侧列出了本系统能管理的项目。',
            '在仪表盘，用户可以管理本系统的所有项目。',
        ],
        'list-index' => [
            '点击左侧菜单栏的{$module_name}后，可以打开{$module_name}列表，查看所有{$module_name}。并对其进行增加、删除、编辑、查询等操作。',
            '从左侧的菜单中，点击{$module_name}便可以打开{$module_name}列表，查看所有{$module_name}。可以对每一条记录分别执行增删改查操作。',
        ],
        'list-create' => [
            '在列表的右上角有一个绿色的加号按钮。点击该按钮即可跳转到添加页面，完成对新记录的创建。',
            '点击页面右边的绿色+号按钮，就会跳转到添加页面，可以在那里新加一行记录。',
            '需要添加新记录的时候，可以点击页面右上方的绿色背景的加号按钮。点击会打开添加页面。',
        ],
        'list-view' => [
            '点击列表中一条记录右侧的眼睛图案即可跳转到查看页面进行查看。',
            '点击操作列中的最左侧的眼睛按钮即可跳转到查看页面进行查看。',
            '在每一行的最右侧有个操作列，点击其中最左侧的眼睛图案即可查看该记录的详细信息。',
        ],
        'list-edit' => [
            '在列表中选择一行，在其右边有一列操作列。点击其中的中间按钮，即可跳转到编辑页面对该数据进行修改。',
            '在列表中选择一行，在其右边有一列操作列。点击中间笔型按钮，即可跳转到编辑页面对该数据进行修改。',
            '在每一行的最右侧有一列为操作，点击其中的笔型按钮即会跳转到编辑页面进行编辑。'
        ],
        'list-delete' => [
            '如果需要删除不需要的记录，可以点击列表最右侧一列中，最右侧的红色垃圾桶按钮进行删除。',
            '每一行的右侧有一列操作列，点击其中最右侧的红色按钮即可删除该行记录。',
            '点击操作列中最右侧红色的垃圾桶按钮，即可实现对该记录的删除。',
        ],
    ];

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
        // prepare doc
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName($this->settings['font-name']);
        $phpWord->setDefaultFontSize($this->settings['font-size']);
        $phpWord->setDefaultParagraphStyle($this->settings);

        $section = $phpWord->addSection($this->settings);

        // 页眉标注软著名称及版本号,并在右上角标注页码
        $header = $section->addHeader();
        $header->addPreserveText($this->option('title'), null, ['alignment' => Jc::CENTER]);
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
        $rand = rand(0, count($this->texts['browse'])-1);
        $section->addText($this->texts['browse'][$rand]);
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
        $rand = rand(0, count($this->texts['login'])-1);
        $text = $this->texts['login'][$rand];
        $modules = array_map(function($item){
            return __('models/'.$item.'.menus.backend.main');
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
        $rand = rand(0, count($this->texts['dashboard'])-1);
        $section->addText($this->texts['dashboard'][$rand]);
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
                $fullpath = realpath($path.DIRECTORY_SEPARATOR.$filename);
                list($width, $height) = getimagesize($fullpath);
                $rate = 0.3;
                $settings = [
                    'alignment' => Jc::CENTER,
                    'height' => $height * $rate,
                    'width' => $width * $rate,
                ];
                $key = 'list-'.$action;
                $rand = rand(0,count($this->texts[$key])-1);
                $text = str_replace('{$module_name}', __('models/'.$model.'.menus.backend.main'), $this->texts[$key][$rand]);
                $section->addText($text);
                $section->addImage($fullpath, $settings);
                $section->addTextBreak();
            }
        }

        $objWriter = IOFactory::createWriter($phpWord);
        $objWriter->save($output);

        return true;
    }
}

