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
* @param type 存储字段
* @param checkData 复选数据对象
* @param field 选择对象中的数据字段键名
* */

// 直接函数
checkBtn(index, value, field) {
    if(this.parmloca.leixing_id.length >= 3) {
        if(this.parmloca.leixing[field][index].checked == false) {
            uni.showToast({
                title: "最多选3中类型",
                icon: 'none',
                duration: 2000
            });
            return
        }
    }
    this.checkFun(this, index, value, "leixing_id", "leixing", field)
},

// 辅助函数
checkFun(that, index, value, type, checkData, field) {
    // let that = this
    var itemType = that.parmloca[type]
    if(itemType.length == 0) {
        itemType.push(value)
        that.parmloca[checkData][field][index].checked = true
        return
    }
    var item = that.parmloca[checkData][field][index]
    if(that.has(itemType, value)) {
        item.checked = false
        for (var i = 0, len = itemType.length; i < len; i++) {
            if(itemType[i] == value) {
                itemType.splice(i, 1);
            }
        }
    } else {
        item.checked = true
        itemType.push(value)
    }
    console.log(itemType)
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
