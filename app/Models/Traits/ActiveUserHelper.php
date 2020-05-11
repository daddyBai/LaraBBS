<?php

namespace App\Models\Traits;

use App\Models\Topic;
use App\Models\Reply;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait ActiveUserHelper
{
    // 临时存放用户数据
    protected $users = [];

    protected $topic_weight = 4; // 话题权重
    protected $reply_weight = 1; // 回复权重
    protected $pass_days = 7; // 多少天内发表过内容
    protected $user_number = 6; // 取出来多少用户

    protected $cache_key = 'larabbs_active_users';
    protected $cache_expire_in_seconds = 65 * 60;

    public function getActiveUsers()
    {
        return Cache::remember($this->cache_key, $this->cache_expire_in_seconds, function (){
            return $this->calculateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        // 取得活跃用户列表
        $active_users = $this->calculateActiveUsers();

        $this->cacheActiveUsers($active_users);
    }

    public function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        // 数组按照得分排序 数组排序
        $users = Arr::sort($this->users, function ($user){
            return $user['score'];
        });

        // 我们需要的是倒叙，高分考前，第二个参数为保持数组的 key 不变
        $users = array_reverse($users, true);

        // 只获取我们想要的数量
        $users = array_slice($users, 0 ,$this->user_number, true);

        // 新建一个空集合
        $active_users = collect();

        foreach ($users as $user_id => $user) {
            // 找寻下是否可以找到用户
            $user = $this->find($user_id);

            // 如果数据库里有该用户的话
            if($user){
                // 将次用户实体放入集合的末尾
                $active_users->push($user);
            }
        }

        return $active_users;
    }

    private function calculateTopicScore(){
        // 从话题数据表中取出限定时间范围内的( $pass_days )，发表过话题的用户
        // 并且同时去除用户此段时间内发布回复的数量
        $topic_users = Topic::query()
            ->select(DB::raw('user_id, count(*) as topic_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
            ->groupBy('user_id')
            ->get();

        foreach ($topic_users as $value){
            $this->users[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }

    private function calculateReplyScore(){
        // 从话题数据表中取出限定时间范围内的( $pass_days )，发表过话题的用户
        // 并且同时去除用户此段时间内发布回复的数量
        $reply_users = Reply::query()
            ->select(DB::raw('user_id, count(*) as reply_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
            ->groupBy('user_id')
            ->get();

        foreach ($reply_users as $value){
            $reply_score = $value->reply_count * $this->reply_weight;
            if(isset($this->users[$value->user_id])){
                $this->users[$value->user_id]['score'] += $reply_score;
            }else{
                $this->users[$value->user_id]['score'] = $reply_score;
            }
        }
    }

    private function cacheActiveUsers($active_users){
        // 缓存
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_seconds);
    }
}
