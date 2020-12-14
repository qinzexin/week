<?php

namespace app\article\controller;

use think\Cache;
use think\Controller;
use think\Db;
use think\Request;

class Data extends Controller
{
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function index()
    {
        return view("insert");
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function create()
    {
        //文章列表显示，并可根据文章标题关键字筛选，并在输入框保留关键字
        $data = model('article')->where("time")->order('time desc')->paginate(10);
        return view("show",['data'=>$data]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收参数
        $param = $request->param();
        $file = request()->file('img');
        //验证
        $result = $this->validate(
            $param,
            [
                'name'  => 'require|max:50',
                'desc'   => 'require',
                'text'   => 'require'
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            dump($result);
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $image = "/uploads/".$info->getSaveName();
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        $param['img'] = $image;
//        插入数据库
        $data = model("Article")->allowField(true)->save($param);
        if ($data){
            Cache::set("article",$param);
            $this->redirect('data/create');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request)
    {
        //接收参数
        $param = $request->param();
        //验证
        $result = $this->validate(
            $param,
            [
                'name'  => 'require|max:50',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            dump($result);
        }
        $data = model("Article")->where('name','like',"%$param%")->select();
        return view("show",['data'=>$data]);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除
        $data = model('Article')
            ->where('id',"in".$id)
            ->where('type',0)
            ->delete();
        dump($data);
        if ($data){
            $this->redirect('data/create');
        }else{
            return  "<script>alert('已经显示无法删除')</script>";
              $this->redirect('data/create');
        }
    }
}
