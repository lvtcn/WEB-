/*
* Name: VueRequst
* */
function get_info_action(that, url, data, datatype, datafleid) {
    that.$http.get(apiUrl + url, data)
        .then((response) => {
            if (response.status === 200) {
                var body = response.body;
                if (body.code === 1) {
                    datafleid = datafleid ? datafleid : 'paradata'
                    // 数据赋值
                    that[datafleid] = body
                    if (datatype == "list") {
                        that[datafleid].data = that[datafleid].data.concat(body.data)
                    }
                    // page dealwith
                    if(that[datafleid].data.total) {
                        that.pageFun(that, that.getstandardlist.data.total, that.pageTurner.pagecur)
                    }
                }
            }
        }, (response) => {
            console.log(response)
        }).catch(function (response) {
        console.log(response)
    })
}

/*
* Name: Web-Vue.js Page function
* @param that Belong to this
* @param total dataNum
* @param current currentPage
* @Inherit Vue Data.pageTurner
* */

<ul class="pagination" style="display: flex; justify-content: center; width: 100%;"
    v-if="pageTurner.pagelist.home">
    <li><a v-on:click="pageClick(pageTurner.pagelist.home)">‹‹</a></li>
    <li><a v-on:click="pageClick(pageTurner.pagelist.prepage)">‹</a></li>
    <!--当前页背景色为蓝色-->
    <li v-for="(item, index) in pageTurner.pagelist.pagestr"
        v-bind:class="item.selected == 1 ? 'active' : ''">
        <a v-on:click="pageClick(item.value)">{{ item.value }}</a>
    </li>
    <li><a v-on:click="pageClick(pageTurner.pagelist.nextpage)"> › </a></li>
    <li><a v-on:click="pageClick(pageTurner.pagelist.last)">››</a></li>
    <li><a>共<i>{{pageTurner.pagelist.last}}</i>页</a></li>
</ul>

// 翻页Data配置
let = pageTurner: {
    pagecur: 1,
    pagesize: 2,
    pagelist: {}
}
pageFun: function (that, $total, $current) {
    // 变量定义
    let $p1, $p2, $pagenum, $nextpage, $prepage, $pagestr = [], $pagei = 0;
    // 总页数
    $pagenum = Math.ceil($total / that.pageTurner.pagesize);

    // 下一页
    $nextpage = $current >= $pagenum ? $pagenum : $current + 1;
    // 上一页
    $prepage = $current <= 1 ? 1 : $current - 1;
    // 开始
    $p1 = $current - 3;
    $p1 = $p1 < 1 ? 1 : $p1;
    // 结束
    $p2 = 6 + $p1;
    $p2 = $p2 > $pagenum ? $pagenum : $p2;

    // 显示页数处理
    for (var $ii = $p1; $ii <= $p2; $ii++, $pagei++) {
        if ($ii === $current) {
            $pagestr[$pagei] = {value: $ii, selected: 1};
        } else {
            $pagestr[$pagei] = {value: $ii, selected: 0};
        }
    }
    // 翻页数据集合
    that.pageTurner.pagelist = {
        pagestr: $pagestr,
        nextpage: $nextpage,
        prepage: $prepage,
        home: 1,
        last: $pagenum,
    }
    // console.log(that.pageTurner.pagelist)
}
