<?php

namespace Ruanzhu\Generators;

use Illuminate\Console\Command;

class RuanzhuEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruanzhu:env
        {-t|--title : 软件名称，默认为项目名称}
        {-o|--outfile=env.txt : 输出文件（txt格式），默认为当前目录的env.txt}
    ';

    protected $description = 'Generate environment information for software copyright.';

    protected $items = [
        '开发的硬件环境',
        '运行的硬件环境',
        '开发该软件的操作系统',
        '软件开发环境/开发工具',
        '该软件的运行平台/操作系统',
        '软件运行支撑环境/支持软件',
        '编程语言',
        '源程序量',
        '开发目的',
        '面向领域/行业',
    ];

    protected $envs = [
        'cpu' => [
            'Intel 酷睿i3 12100',
            'Intel 酷睿i3 12100F',
            'Intel 酷睿i3 10105',
            'Intel 酷睿i3 10100',
            'Intel 酷睿i3 10105F',
            'Intel 酷睿i3 8100',
            'Intel 酷睿i3 9100F',
            'Intel 酷睿i3 9100',
            'Intel 酷睿i3 10100F',
            'Intel 酷睿i3 8350K',
            'Intel 酷睿i3 9350KF',
            'Intel 酷睿i5 12490F',
            'Intel 酷睿i5 12400F',
            'Intel 酷睿i5 10400F',
            'Intel 酷睿i5 12400',
            'Intel 酷睿i5 10400',
            'Intel 酷睿i5 9400F',
            'Intel 酷睿i5 11400',
            'Intel 酷睿i5 10500',
            'Intel 酷睿i5 9400',
            'Intel 酷睿i5 10600KF',
            'Intel 酷睿i5 11400F',
            'Intel 酷睿i5 9600KF',
            'AMD Ryzen 5 3600',
            'AMD Ryzen 5 5500',
            'AMD Ryzen 5 3500X',
            'AMD Ryzen 5 5600',
            'AMD Ryzen 5 3400G',
            'AMD Ryzen 5 4500',
            'AMD Ryzen 5 PRO 4650G',
            'AMD Ryzen 3 2200G',
            'AMD Ryzen 3 3200G',
            'AMD Ryzen 3 3100',
            'AMD Ryzen 3 4100',
            'AMD Ryzen 3 3300X',
            'AMD Ryzen 3 PRO 3200G',
        ],
        'mem' => [
            '32GB',
            '16GB',
            '8GB',
        ],
        'graphic-mem' => [
            '4GB',
            '3GB',
            '2GB',
        ],
        'storage' => [
            '固态硬盘256G(SSD)',
            '固态硬盘128G(SSD)',
            '固态硬盘100G(SSD)',
            '固态硬盘50G(SSD)',
            '机械硬盘1T(HDD)',
            '机械硬盘500G(HDD)',
            '机械硬盘320G(HDD)',
        ],
        'dev-os' => [
            'Windows 11',
            'Windows 10',
            'Windows 8',
            'Windows 7',
            'macOS 12',
            'macOS 11',
            'macOS 10.15',
            'macOS 10.14',
            'macOS 10.13',
            'macOS 10.12',
            'macOS 10.11',
            'macOS 10.10',
            'Ubuntu 20.04',
            'Ubuntu 21.04',
            'Ubuntu 22.04',
            'debian 9',
            'debian 10',
            'debian 11',
        ],
        'deploy-os' => [
            'Ubuntu 20.04',
            'Ubuntu 21.04',
            'Ubuntu 22.04',
            'debian 9',
            'debian 10',
            'debian 11',
        ],
        'dev-soft' => [
            'SublimeText 3',
            'SublimeText 4',
            'PhpStorm 2022',
            'PhpStorm 2021',
            'PhpStorm 2020',
            'Visual Studio Code',
            'VS Code',
            'EMEditor',
            'Notepad++',
        ],
        'deploy-soft' =>[
            'Apache 2.4',
            'Apache 2.3',
            'Apache 2.2',
            'Apache 2',
            'nginx 1.23',
            'nginx 1.22',
            'nginx 1.20',
            'nginx 1.19',
            'nginx 1.17',
        ],
        'lang' => [
            'HTML+PHP',
            'HTML+CSS+PHP',
            'HTML+CSS+JS+PHP',
            'HTML+CSS+JavaScript+PHP',
            'HTML+JavaScript+PHP',
            'HTML+PHP8',
            'HTML+CSS+PHP8',
            'HTML+CSS+JS+PHP8',
            'HTML+CSS+JavaScript+PHP8',
            'HTML+JavaScript+PHP8',
        ],
        'amount' => [10000, 1000000],
        'purpose' => [
            '企业内部使用',
        ],
        'industry' => [
            '技术服务',
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $title = $this->option('title');
        if (!$this->hasOption('title')){
            $title = config('app.name');
        }
        $this->info('开始生成软著运行环境 '.$this->title);

        $out = $this->option('outfile');

        $path = base_path($out);
        $file = fopen($path, 'w');

        $func = [
            'generateDevHardwareEnv',
            'generateDeployHardwareEnv',
            'generateDevOS',
            'generateDevSoft',
            'generateDeployOS',
            'generateDeploySoft',
            'generateLang',
            'generateAmount',
            'generatePurpose',
            'generateIndustry',
        ];
        $appname = '软件名称：'.$title."\n\n";

        fwrite($file, $appname);
        foreach ($this->items as $key => $item) {
            $text = ($key+1).'、'.$item.'：'.($this->{$func[$key]}()).'；';
            $text .= "\n\n";
            fwrite($file, $text);
        }
        fclose($file);

        $this->success('运行环境文件生成结束');
    }

    protected function generateDevHardwareEnv()
    {
        $envs = [
            'CPU：'.$this->envs['cpu'][array_rand($this->envs['cpu'])],
            '内存容量：'.$this->envs['mem'][array_rand($this->envs['mem'])],
            '显存容量：'.$this->envs['graphic-mem'][array_rand($this->envs['graphic-mem'])],
            '硬盘类型：'.$this->envs['storage'][array_rand($this->envs['storage'])],
        ];

        return implode('，', $envs);
    }

    protected function generateDeployHardwareEnv()
    {
        $envs = [
            'CPU：'.$this->envs['cpu'][array_rand($this->envs['cpu'])],
            '内存容量：'.$this->envs['mem'][array_rand($this->envs['mem'])],
            '显存容量：'.$this->envs['graphic-mem'][array_rand($this->envs['graphic-mem'])],
            '硬盘类型：'.$this->envs['storage'][array_rand($this->envs['storage'])],
        ];

        return implode('，', $envs);
    }

    protected function generateDevOS()
    {
        return $this->envs['dev-os'][array_rand($this->envs['dev-os'])];
    }

    protected function generateDevSoft()
    {
        return $this->envs['dev-soft'][array_rand($this->envs['dev-soft'])];
    }

    protected function generateDeployOS()
    {
        return $this->envs['deploy-os'][array_rand($this->envs['deploy-os'])];
    }

    protected function generateDeploySoft()
    {
        return $this->envs['deploy-soft'][array_rand($this->envs['deploy-soft'])];
    }

    protected function generateLang()
    {
        return $this->envs['lang'][array_rand($this->envs['lang'])];
    }

    protected function generateAmount()
    {
        return rand($this->envs['amount'][0], $this->envs['amount'][1]);
    }

    protected function generatePurpose()
    {
        return $this->envs['purpose'][array_rand($this->envs['purpose'])];
    }

    protected function generateIndustry()
    {
        return $this->envs['industry'][array_rand($this->envs['industry'])];
    }
}