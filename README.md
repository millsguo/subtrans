# subtrans
使用机器翻译电影或剧集的字幕

```
docker pull millsguo/subtrans
```

## 安装后，配置环境变量，暂时仅支持aliyun的翻译接口
````
API_NAME=aliyun
USE_PRO=true #是否使用专业翻译接口，专业接口稍贵一点，但也有100W字符的免费额度
ACCESS_KEY_1=#阿里云的ACCESS_KEY#
ACCESS_SECRET_1=#阿里云的ACCESS_SECRET#
USE_PRO_2=true #是否使用第二个接口
ACCESS_KEY_2=#ACCESS_KEY
ACCESS_SECRET_2=#ACCESS_SECRET
EANBLE_TRANS=true #是否启用翻译
````

## 再设置挂载目录，目前暂时支持以下6个挂载目录
````
/data/movies-1
/data/movies-2
/data/movies-3
/data/tv-1
/data/tv-2
/data/tv-3
````
 
## 处理逻辑如下

- 检查视频文件是否内置中文字幕，有，忽略此文件；
- 检查视频文件是否内置英文字幕，有，导出英文字幕至视频所在目录
- 检查当前目录是否有外挂中文字幕，有，忽略
- 检查当前目录是否有外挂英文字幕，有，调用翻译接口翻译英文字幕后，保存至视频所在目录；没有，忽略

## 支持处理手动下载的字幕文件
