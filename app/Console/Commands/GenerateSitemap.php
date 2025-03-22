<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Car;
use App\Models\BlogPost;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap';

    public function handle()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency('daily'))
            ->add(Url::create('/about-us')
                ->setPriority(0.8)
                ->setChangeFrequency('monthly'))
            ->add(Url::create('/car-rental')
                ->setPriority(0.9)
                ->setChangeFrequency('weekly'))
            ->add(Url::create('/services')
                ->setPriority(0.8)
                ->setChangeFrequency('monthly'))
            ->add(Url::create('/contact-us')
                ->setPriority(0.7)
                ->setChangeFrequency('monthly'))
            ->add(Url::create('/blog')
                ->setPriority(0.8)
                ->setChangeFrequency('weekly'));
        
        // Add car pages
        Car::all()->each(function (Car $car) use ($sitemap) {
            $sitemap->add(Url::create("/car/{$car->slug}")
                ->setPriority(0.8)
                ->setChangeFrequency('weekly'));
        });
        
        // Add blog posts
        BlogPost::all()->each(function (BlogPost $post) use ($sitemap) {
            $sitemap->add(Url::create("/blog/{$post->slug}")
                ->setPriority(0.7)
                ->setChangeFrequency('monthly'));
        });
        
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated successfully');
    }
}