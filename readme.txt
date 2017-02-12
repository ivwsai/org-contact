注意事项：

1，请手动修改index.php中的关键项目：调试开关、站点目录、模拟开关、语言包目录、日志配置等项目的正确性；

2，请确保application/cache、application/views_obj、application/logs 三个目录可读写，否则系统可能出错

3，项目的应用配置文件请放在application/config目录下

4，如果使用apache服务器，请修改.htaccess文件确保RewriteBase目录的正确配置



开发环境：
#生成image
docker-compose build

#启动服务
docker-compose up
