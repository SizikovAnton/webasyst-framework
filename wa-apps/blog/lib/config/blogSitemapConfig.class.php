<?php
class blogSitemapConfig extends waSitemapConfig
{
    public function execute()
    {
        $routes = $this->getRoutes();
        $app_id = wa()->getApp();

        $blog_model = new blogBlogModel();
        $post_model = new blogPostModel();

        $blogs = $blog_model->getAvailable(false,array('id','name','url'));

        foreach ($routes as $route) {
            $lastmod = null;
            $this->routing->setRoute($route);
            $default_blog_id = isset($route['blog_url_type']) ? (int)$route['blog_url_type'] : 0;
            $default_blog_id = max(0, $default_blog_id);
            foreach ($blogs as $blog_id => $blog) {
                if (!$default_blog_id || ($blog_id == $default_blog_id) ) {
                    $posts = $post_model->search(array('blog_id'=>$blog_id), array('blog'=>$blogs))->fetchSearchAll('id,title,url,datetime,blog_id');
                    foreach ($posts as $post) {
                        $post['blog_url'] = $blog['url'];
                        $post_lastmod = max(strtotime($post['datetime']), strtotime($post['comment_datetime']));
                        $lastmod = max($lastmod, $post_lastmod);
                        $this->addUrl($post['link'], $post_lastmod);
                    }
                }
            }

            $this->addUrl(wa()->getRouteUrl($app_id."/frontend", array(), true), $lastmod);
        }
    }
}