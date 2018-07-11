<template>
    <div class="container main" id="library">
        <div class="row">
            <div class="col-sm-4 col-md-3" id="libraryMenu">
                <!-- can be used to toggle multiple different language -->
                <!-- <div :class="['btn-group', 'd-none', 'd-sm-block', 'd-md-none']" role="group" aria-label="..." v-for="lang in languages">
                    <button type="button" :class="['btn', currentLang == lang ? 'btn-invert' : 'btn-default']" @click="changeLang(lang)">{{ lang.charAt(0).toUpperCase() + lang.slice(1) }}</button>
                </div>-->
                <h1>Table of Contents</h1>
                <br>
                <div v-for="article in wiki"
                     :key="article.article_id"
                     @click="showContent(article.article_id)"
                     class="menubtn" >

                    <h4 class="article-title">{{article.title}}</h4>
                    <p :id="'title-' + article.article_id"> </p>

                </div>
            </div>
            <div class="col-sm-8 col-md-9" id="content-container">
                <vue-markdown :source="currentContent"
                              :toc="true"
                              :toc-id="currentTitle"
                              :toc-anchor-link="false"
                              v-on:toc-rendered="receiveToc">
                </vue-markdown>
            </div>
        </div>

    </div>
</template>

<script>
    import { mapGetter, mapActions } from 'vuex'
    import VueMarkdown from 'vue-markdown'


    export default {

        mounted() {
            this.getAllToc();
        },
        components: {
            "vue-markdown": VueMarkdown

        },
        props: ['wiki'],
        data: function () {
            return {

                currentTitle:"",
                currentContent:"",
                currentLang:"spanish",
                currentArticleId: 1,
                languages: ['spanish']
            }
        },
        methods: {
            showContent: function (id) {
                let content = this.wiki.find( title => title.article_id == id);

                this.currentTitle = content.title;
                this.currentContent = content.content;
                this.currentArticleId = id;

            },
            /*changeLang: function (lang) {
                this.currentLang = lang;
                this.showContent(this.currentArticleId);

            },*/
            getAllToc: function() {
                for(let i in this.wiki){
                    let article = this.wiki[i];
                    let appScope = this;

                    // loops through and sets article variables to retrieve all toc items for each article
                    setTimeout(function(){
                        appScope.currentTitle = article.title;
                        appScope.currentContent = article.content;
                        appScope.currentArticleId = article.article_id;
                      }, 100);
                }
            },
            receiveToc: function (tocHtmlStr) {
                let title = "#title-" + this.currentArticleId;

                if( $(title).children().length <= 0 ) {
                    $(title).append(tocHtmlStr);

                    // css needs to be apended dynamically to the added TOC items
                    $(".table-of-contents").css('list-style-type', 'none');
                    $(".table-of-contents").find('ul').css('list-style-type', 'none');
                    $(".table-of-contents").find('a').css('color', '#636b6f');
                    $(".table-of-contents").find('li').css('margin-left', '-10px');
                }
            }

        }
    }
</script>

<style scoped lang="scss">
    @import "../../../sass/_variables.scss";
    .menubtn {
        cursor:pointer;
        margin-bottom: 3rem;
    }
    .table-of-contents ul {
        list-style-type: none;
    }
    .article-title:hover{
        text-decoration: underline;
    }
    #library {
        margin-top: 0px;
        //box-shadow: 0px -6px 10px;
    }

    #libraryMenu {
        padding-top: 40px;
        padding-left: 40px;
        box-shadow: inset -7px 0 9px -10px rgba(0,0,0,0.4);
    }

    #content-container {
        padding-right: 80px;
        padding-left: 40px;
        padding-top: 40px;

    }
    #content-container h1 {
        margin-top: 0px;
    }

</style>
