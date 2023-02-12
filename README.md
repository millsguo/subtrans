# subtrans
使用机器翻译自动翻译电影和剧集的字幕，最新剧集不用苦等字幕

```
docker pull millsguo/subtrans
```

### 第一步，配置存储空间，设置挂载目录，目前暂时支持以下6个挂载目录
````
#主机目录        docker装载路径
/应用配置目录     /app/subtrans/config
/第一个电影目录   /data/movies-1
/第二个电影目录   /data/movies-2
/第三个电影目录   /data/movies-3
/第一个剧集目录   /data/tv-1
/第二个剧集目录   /data/tv-2
/第三个剧集目录   /data/tv-3
````

### 第二步，在应用配置目录创建config.ini文件，增加以下内容，暂时仅支持aliyun的翻译接口
````
[translation]
api_name = aliyun
;是否启用机器翻译，不启用设为false
enable_trans = true

;阿里云的ACCESS_KEY#
aliyun1.access_key = ####
;阿里云的ACCESS_SECRET
aliyun1.access_secret = ####
;是否使用专业翻译接口，专业接口稍贵一点，但也有100W字符的免费额度
aliyun1.use_pro = true 

;第二个阿里云的ACCESS_KEY#
aliyun2.access_key = ####
;第二个阿里云的ACCESS_SECRET
aliyun2.access_secret = ####
;是否使用专业翻译接口，专业接口稍贵一点，但也有100W字符的免费额度
aliyun2.use_pro = true

;以下为需要扫描的挂载路径，请不要轻易更改
[volume]
movies-1 = /data/movies-1
movies-2 = /data/movies-2
movies-3 = /data/movies-3
tv-1 = /data/tv-1
tv-2 = /data/tv-2
tv-3 = /data/tv-3
````

### 处理逻辑如下

- 检查视频文件是否内置中文字幕，有，忽略此文件；否，执行下一步
- 检查视频文件是否内置英文字幕，有，导出英文字幕至视频所在目录；
- 检查当前目录是否有外挂中文字幕，有，忽略
- 检查当前目录是否有外挂英文字幕，有，调用翻译接口翻译英文字幕后，保存至视频所在目录；没有，忽略

### 支持处理手动下载的字幕文件

- 手动下载剧集字幕文件请使用s01e01.zip之类格式
- 手动下载电影字幕文件请使用s00e00.zip命名
- 整季字幕包请使用s01.zip命名
- 所有字幕文件保存到视频目录