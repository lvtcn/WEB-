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

<ul class="pagination" v-if="pageTurner.pagelist.home">
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
function pageFun(that, $total, $current) {
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

/*
* Name: 获取URL参数
* */
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

/*
* Name: Vue自定义复选框函数
* @param index 所在for序列index
* @param value 数值
* @param type 所在数组名称
* */
checkFun(index, value, type) {
    let that = this
    console.log(that[type].length)
    if(that[type].length == 0) {
        that[type].push(value)
        that.checkData[type][index].check = true
        return
    }
    var item = that.checkData[type][index]
    if(that.has(that[type], value)) {
        item.check = false
        for (var i = 0, len = that[type].length; i < len; i++) { //遍历数组的值
            if(that[type][i] == value) {
                delete that[type][i];
            }
        }
        for (var i = 0, len = that[type].length, check=[]; i < len; i++) { //遍历数组的值
            if(that[type][i]) {
                check.push(that[type][i])
            }
        }
        that[type] = check
    } else {
        item.check = true
        that[type].push(value)
    }
    console.log(that[type])
},
    
/*
* Name: Vue数组包含检测函数
* */
has(arr, num) {
    var bool = false; //默认不在数组中
    for (var i = 0, len = arr.length; i < len; i++) { //遍历数组的值
        if (arr[i] == num) {
            bool = true; //若存在某个值与改值相等，bool值为true
        }
    }
    return bool; //返回bool
}
