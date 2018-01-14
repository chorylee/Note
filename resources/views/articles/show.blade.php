@extends('layouts.app')

@section('title',$article->title)

@section('content')

    <article-view inline-template :initial-comments-count="0" :article-id="{{ $article->id }}">
        
        <div class="columns article-page">

            <div class="column is-9">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title article-title">
                            {{ $article->title }}
                        </p>

                    </header>
                    <div class="card-content">
                        <div class="content">
                            <div class="article">
                                <parse :content="{{ $article->body }}"></parse>
                            </div>
                        </div>



                        <div class="columns">
                            <div class="column is-8 is-hidden-mobile">
                                <div class="social-share-container">
                                    <div class="social-share" data-disabled="google" data-description="Share.js - 一键分享到微博，QQ空间，腾讯微博，人人，豆瓣"></div>
                                </div>
                            </div>

                            <div class="column is-4" style="line-height: 30px">


                                @can('update',$article)
                                <div class="is-pulled-right m-l-20">
                                    <b-tooltip label="编辑">
                                        <a class="popover-with-html" href="{{ route('articles.edit',$article) }}">
                                            <i class="fa fa-edit"></i> <span></span>
                                        </a>
                                    </b-tooltip>
                                </div>


                                <div class="is-pulled-right m-l-20">
                                    <b-tooltip label="删除">
                                        <a class="popover-with-html" @click="confirmDelete()">
                                            <i class="fa fa-trash"></i> <span></span>
                                        </a>
                                    </b-tooltip>
                                </div>
                                @endcan

                                <article-subscribe :initial-subscribed="{{ (int)$article->isSubscribed }}" :article-id = {{ $article->id }}></article-subscribe>

                            </div>
                        </div>

                    </div>



                </div>

                <comments></comments>

            </div>

            <div class="column is-3">
                <div id="sticker">
                    @include('articles.partials.user')
                </div>
            </div>
        </div>
        
    </article-view>



@endsection

@section('scripts')

    <script type="text/javascript">


        $(window).scroll(function (event) {

            if ($(window).width() <= 991) {
                $("#sticker").unstick();
                return;
            }
            var top = $(window).scrollTop();

            if (top > 300){
                $("#sticker").sticky({topSpacing:20});
            }else{

                $("#sticker").unstick();
            }

            console.log(top);

        });

    </script>

@endsection

