<?php

namespace App\Models;

use App\Base\Fitters\DiscussionFilters;
use App\Base\Traits\ContentSetable;
use App\Base\Traits\Favoritable;
use App\Base\Traits\RecordsActivity;
use App\Base\Traits\SlugTransable;
use App\Base\Traits\Subscribable;
use App\Jobs\TranslateSlug;
use App\Notifications\DiscussionWasFavorited;
use App\Notifications\DiscussionWasSubscribed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Discussion extends Model
{

    use RecordsActivity,
        Subscribable,
        Favoritable,
        ContentSetable,
        SlugTransable,
        SoftDeletes;

    protected $guarded = [];

    protected $with = ['user','tags','bestAnswer'];

    protected $appends = [
        'isSubscribed',
        'favoritesCount' => 'favorites_count',
        'isFavorited' => 'is_favorited',
        'lastComment'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function tags()
    {
        return $this->morphToMany(Tag::class,'taggable');
    }


    public function drafts()
    {
        return $this->morphMany(Draft::class,'relation');
    }

    public function currentDraft(){
        return $this->belongsTo(Draft::class,'draft_id','id');
    }



    public function comments()
    {
        return $this->morphMany(Comment::class,'commentable');
    }

    public function hasComment($comment_id){

        return !!$this->comments()->where('id',$comment_id)->first();
    }

    public function bestAnswer(){
        return $this->belongsTo(Comment::class,'solved_id','id');
    }

    public function scopeFilter($query, DiscussionFilters $filters)
    {
        return $filters->apply($query);
    }

    public function getlastCommentAttribute(){

        $lastComment = $this->comments()->latest()->first();
        return $lastComment;
    }

    public function syncTags($tags){

        $tags = collect($tags)->map(function ($tag){

            if (!is_array($tag)){
                $tag = Tag::query()->firstOrCreate([
                    'name' => $tag
                ]);
                return $tag->id;
            }

            $tag = Tag::where('id',$tag['id'])
                ->orWhere('name',$tag['name'])
                ->firstOrCreate([
                    'name' => $tag['name']
                ]);
            return $tag->id;
        });
        $this->tags()->sync($tags);
        return $this;

    }


    public function getBriefAttribute(){

        $body = $this->body;
        $html = json_decode($body,true)['html'];
        $text = strip_tags($html);
        return mb_substr($text,0,200);

    }

    public function path($params = [])
    {
        return route('discussions.show', array_merge([$this->id, $this->slug], $params));
    }


    public function notify(){

        $user = $this->user;
        if (Auth::user()->id == $user->id){
            return;
        }

        $user->notify(new DiscussionWasSubscribed($this));
    }




    public function hasUpdatesFor($user){
        if (empty($user)) return true;

        $key = $user->visitedDiscussionCacheKey($this);
        return $this->updated_at > cache($key);

    }

    public function notifyFavorited(){

        $user = $this->user;
        if (Auth::user()->id == $user->id){
            return;
        }
        $user->notify(new DiscussionWasFavorited($this));

    }
}
