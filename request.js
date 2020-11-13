let baseUrl = ''
if (process.env.NODE_ENV === 'development')
{
  console.log('开发环境')
  baseUrl = 'http://192.168.1.103:8001'
} else
{
  console.log('生产环境')
  baseUrl = 'https://test.njbs.yhbs.cn/api'
}

export default (params) => {
  // 加载中
  uni.showLoading({
    title: '加载中'
  });
  let token = uni.getStorageSync('TOKEN');
  return new Promise((resolve, reject) => {
    let header = {};
    if (params.params)
    {
      header = { "Content-Type": "application/x-www-form-urlencoded" };
      params.data = params.params;
    }
    params.url = baseUrl + params.url;
    wx.request({
      ...params,
      header: {
        token: token ? token : "",
        ...header
      },
      success: (res) => {
        resolve(res)
      },
      fail: (err) => {
        reject(err)
      },
      complete () {
        uni.hideLoading()
      }
    })
  })
}


export const uploadFile = (tempFilePaths, fn) => {
  wx.showLoading({ title: "正在上传..." });
  wx.uploadFile({
    url: baseUrl + '/oss/back-oss/upload',
    filePath: tempFilePaths[0],
    name: 'file',
    success (res) {
      wx.hideLoading();
      const data = res.data
      console.log(data);
      console.log("上传成功****");
      var d = JSON.parse(data);
      if (d.code == 200)
      {
        fn(d.data);
      } else
      {
        wx.showToast({ title: d.msg, icon: "none" });
      }
      console.log("上传文件路径");
    },
    fail () {
      wx.hideLoading();
      wx.showToast({ title: "网络异常,请稍后重试", icon: "none" });
    }
  })
}





