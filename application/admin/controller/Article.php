<?php
namespace app\admin\controller;


class Article extends AdminBase
{
    public function initialize(){
        $this->cates = db('cates')->where(['status'=>1])->select();
        $this->assign('cates',$this->cates);
    }
    public function index()
    {
        $this->assign('title','博文列表');
        $this->assign('add',url('add'));

        $articles = db('articles')->paginate(20)->each(function($v,$k){
            if($v['deleted'] == 1){
                $v['status_text'] = '显示中';
            }elseif($v['deleted'] == 2){
                $v['status_text'] = '回收站';
            }
            if($v['secret'] == 1){
                $v['secret_text'] = '公开';
            }elseif($v['secret'] == 2){
                $v['secret_text'] = '私密';
            }
            $v['manager'] = db('admins')->where(['id'=>$v['manager']])->value('ad_account')??'超级管理员';
            return $v;
        });

        foreach ($articles as $k => $v) {

        }
        $this->assign('articles_list',$articles);
        return $this->fetch();
    }

    public function preview(){
        $this->assign('title','预览博文');
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        
        $article = db('articles')->where(['id'=>$id])->field('art_title,art_content,id,deleted')->find();
        $article['art_content'] = str_replace(UPLOAD_DIR.'temp\\',UPLOAD_DIR, $article['art_content']);
         
        $cate_names = db('articles')
            ->alias('a')
            ->where(['a.id'=>$id])
            ->join('article_cate_relations acm','acm.article_id = a.id')
            ->join('blog_cates c','c.id = acm.cate_id')
            ->column('cate_name');
        $this->assign('cate_names',$cate_names);
        $this->assign('article',$article);
        return $this->fetch();

    }
    public function add(){
        if(input('post.')){
            $data = input('post.');
            $this->process_imags_problem($data['art_content']);
            if(isset($data['file'])){
                unset($data['file']);
            }
            if(!$data['art_title']){
                $this->return_data(0,'博文题目不得为空');
            }
            if(empty($data['cate_ids'])){
                $this->return_data(0,'标签至少选一个');
            }
            if(!$data['art_content']){
                $this->return_data(0,'博文内容不得为空');
            }
            $data['create_time'] = $data['update_time'] = time();
            $data['manager'] = session('admin.id');
            $data['secret'] = 2;
            $data_tmp['cate_ids'] = $data['cate_ids'];
            unset($data['cate_ids']);
            $res = db('articles')->insertGetId($data);
            if($res){
                if(isset($data_tmp['cate_ids'])){
                    foreach ($data_tmp['cate_ids'] as $k => $cate_id) {
                        db('article_cate_relations')->data(['cate_id'=>$cate_id,'article_id'=>$res])->insert();
                    }
                }
                $this->return_data(1,'新增博文成功');
            }else{
                $this->return_data(0,'新增博文失败');
            }
        }
        $this->assign('title','新增博文');
        return $this->fetch();
    }

    public function edit(){
        if(input('post.')){
            $data = input('post.');
            if(!isset($data['has_imgs'])){
                $data['has_imgs'] = [];
            }
            $this->process_imags_problem($data['art_content'],$data['has_imgs']);
            unset($data['has_imgs']);
            if(!$data['id']){
                $this->return_data(0,'没有id');
            }
            if(isset($data['file'])){
                unset($data['file']);
            }
            if(!$data['art_title']){
                $this->return_data(0,'博文题目不得为空');
            }
            if(empty($data['cate_ids'])){
                $this->return_data(0,'标签至少选一个');
            }
            if(!$data['art_content']){
                $this->return_data(0,'博文内容不得为空');
            }
            try{
                $data['update_time'] = time();
                $data['manager'] = session('admin.id');
                if(isset($data['file'])){
                    unset($data['file']);
                }
                $data_tmp['cate_ids'] = $data['cate_ids'];
                unset($data['cate_ids']);
                db('articles')->data($data)->update();
                if (isset($data_tmp['cate_ids'])) {
                    db('article_cate_relations')->where(['article_id'=>$data['id']])->delete();
                    foreach ($data_tmp['cate_ids'] as $k => $cate_id) {
                       db('article_cate_relations')->data(['cate_id' => $cate_id, 'article_id' => $data['id']])->insert();
                    }
                }
                $this->return_data(1,'编辑博文成功');
            }catch (\Exception $e){
                $this->return_data(0,'编辑博文失败');
            }
        }
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        $article = db('articles')->where(['id'=>$id])->find();
        $article['art_content'] = str_replace(UPLOAD_DIR.'temp\\',UPLOAD_DIR, $article['art_content']);
        $this->assign('article',$article);
        $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
        preg_match_all($preg, $article['art_content'], $imgArr);
        $this->assign('imgArr',$imgArr[1]);
        $this->assign('title','编辑博文');
        //得到文章的标签
        $article_cate_ids = db('article_cate_relations')->where(['article_id'=>$id])->column('cate_id');
        if($article_cate_ids){
            $this->assign('article_cate_ids',$article_cate_ids);
        }else{
            $this->assign('article_cate_ids',[]);
        }
        return $this->fetch();
    }

    public function del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
         $art_content = db('articles')->where(['id'=>$id])->value('art_content');
         $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
         preg_match_all($preg, $art_content, $imgArr);
        if(isset($imgArr[1])){
            $old_images = str_replace(UPLOAD_DIR.'temp\\',UPLOAD_DIR, $imgArr[1]);
            foreach ($old_images as $key => $old_image) {
                $old_image = strstr($old_image,'.');
                if(file_exists($old_image)){
                    @unlink($old_image);
                }
            }
        }
        try{
            $res = db('articles')->where(['id'=>$id])->delete();
            if($res){
                $this->success('删除博文成功');
            }else{
                $this->error('删除博文失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有用户在使用该博文，请检查');
            }else{
                $this->success('删除博文成功');
            }
        }
    }

    //修改某一个字段
    public function setField($type=1){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        $field = '';
        $value = '';
        $sinfo = '';
        $tb = db('articles');
        switch ($type){
            case 1://移出回收站
                $field = 'deleted';
                $value = '1';
                $sinfo = '发布成功';
                break;
            case 2://移入回收站
                $field = 'deleted';
                $value = '2';
                $sinfo = '移入回收站成功';
                break;
        }
        if($field && $value){
            try{
                $tb->where(['id'=>$id])->update([$field=>$value]);
                $this->success($sinfo);
            }catch (\Exception $e){
                if($e->getMessage()){
                    $this->return_data(0,'操作失败');
                }else{
                    $this->return_data(1,$sinfo);
                }
            }
        }
        $this->return_data(0,'代码出错');
    }

}
