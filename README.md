# 网络安全攻防演练靶场 v1.1

> 基于 PHP 8.0 + MySQL 5.7 构建的 Web 安全教学平台，固定最高安全等级，覆盖 SQL 注入、XSS、文件上传等典型漏洞场景。所有攻击行为均被记录，可在战败榜复盘。

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql&logoColor=white)
![Version](https://img.shields.io/badge/version-8.0-green)
![License](https://img.shields.io/badge/license-MIT-blue)

## 项目简介

本项目是一个**合法授权的网络安全攻防演练靶场**，面向安全爱好者、学生和初学者，提供真实的 Web 漏洞练习环境。平台模拟了常见的 Web 应用漏洞场景，并在最高安全等级下展示防护手段，帮助学习者理解攻击原理与防御方法。

**作者**：高先生笔记
**网站**：[www.gxsnote.cn](https://www.gxsnote.cn)
**联系方式**：QQ 67031002
**最后更新**：2026-08-15

## 功能模块

| 模块 | 路径 | 说明 |
|------|------|------|
| 注入练习 | `/login/` | SQL 注入登录绕过，PDO 预处理 + WAF 特征拦截 |
| 胜利榜 | `/board/` | 攻陷后写黑页留名（仅管理员可写，仅 HTML/CSS/JS） |
| 战败榜 | `/graveyard/` | 按攻击者 IP 聚合的攻击记录排行 |
| 操作日志 | `/logs/` | 全部请求与拦截记录（仅管理员可见） |
| 留言板 | `/guestbook/` | XSS 练习，输入特征拦截 + 输出转义 |
| 文件上传 | `/upload/` | 白名单后缀 + MIME 校验 + getimagesize + 随机重命名 |
| 注册/改密 | `/register/` `/profile/` | 用户注册与密码修改 |
| 渗透规则 | `/about/` | 授权声明、渗透规则、违规处理 |

## 技术栈

- **后端**：PHP 8.0+（PDO 预处理，无框架）
- **数据库**：MySQL 5.7+（表前缀 `gxs_`）
- **前端**：原生 HTML/CSS/JS，终端风格黑底绿字
- **IP 封锁**：ip2region 离线库，仅允许中国大陆 IP 访问
- **Web 服务器**：Apache / Nginx（推荐宝塔面板）

## 安全特性

### 防护机制

- 固定最高安全等级（SECURE MODE），不可切换
- SQL 注入：PDO 参数化查询 + 关键字 WAF 拦截
- XSS：输出 `htmlspecialchars(ENT_QUOTES)` 转义 + 输入特征拦截
- 文件上传：白名单后缀 + finfo MIME 检测 + getimagesize 图片校验 + 随机重命名
- 危险后缀（php/phtml/phar/asp/jsp 等）直接拦截
- CSRF Token 全站表单防护
- 登录防爆破：15 分钟内最多失败 5 次
- Session Cookie：HttpOnly + SameSite=Lax + Secure（HTTPS）
- 操作日志公开页面仅管理员可查看

### 服务器加固

- `includes/`、`config/`、`preferences/` 目录禁止 Web 访问
- `H/`（黑页）、`uploads/` 目录禁止 PHP 执行
- 禁止访问 `.sql`、`.bak`、`.ini`、`.log` 等敏感文件
- 禁止目录列表（Options -Indexes）
- 黑页仅允许 HTML/CSS/JS，文件名强制 `.html`
- 反序列化漏洞已修复：Cookie 使用 JSON 存储，禁止 `unserialize` 用户输入
- `Cache` 类文件写入限制在 `preferences/` 目录，禁止可执行后缀

## 安装部署

### 环境要求

- PHP >= 8.0（需开启 PDO、finfo、mbstring 扩展）
- MySQL >= 5.7
- Apache（推荐，支持 .htaccess）或 Nginx（需自行配置伪静态规则）

### 快速安装

1. 下载代码到网站根目录：
   ```bash
   git clone https://github.com/gxsnote/target.git /www/wwwroot/yourdomain/
   ```

2. 修改数据库配置：
   编辑 `config/config.php`，填写数据库连接信息：
   ```php
   'db' => [
       'host'   => '127.0.0.1',
       'port'   => 3306,
       'name'   => '你的数据库名',
       'user'   => '你的数据库用户',
       'pass'   => '你的数据库密码',
       'prefix' => 'gxs_',
   ],
   ```

3. 浏览器访问安装向导：
   ```
   http://你的域名/install/install.php
   ```
   安装脚本会自动创建数据表、管理员账号和安全配置文件。

4. **安装完成后立即删除 `install/` 目录！**

5. 默认管理员账号：
   - 用户名：`admin`
   - 密码：`zhuanjiao@123.`（末尾有英文句点）
   - **请登录后立即修改密码**

### 手动安装（命令行）

```bash
mysql -u用户名 -p 数据库名 < install/install.sql
```

### 目录权限

```bash
chmod -R 755 /www/wwwroot/yourdomain/
chmod -R 777 uploads/ H/ preferences/
```

## Nginx 伪静态规则

如果使用 Nginx，请在站点配置中添加以下规则（替代 .htaccess）：

```nginx
# 禁止访问敏感目录
location ^~ /includes/ { deny all; }
location ^~ /config/ { deny all; }
location ^~ /preferences/ { deny all; }

# 禁止访问敏感文件
location ~ \.(sql|bak|ini|log|md|sh|conf)$ { deny all; }

# H 和 uploads 目录禁止 PHP 执行
location ~* ^/(H|uploads)/.*\.(php|php3|php5|phtml|phar|asp|aspx|jsp|cgi)$ {
    deny all;
}
```

## 目录结构

```
├── index.php              # 首页
├── page.php               # 帮助文档路由
├── .htaccess              # 根目录安全配置
├── config/
│   ├── config.php         # 数据库与站点配置
│   └── waf.php            # WAF 拦截入口
├── includes/
│   ├── init.php           # 初始化入口
│   ├── functions.php      # 公共函数库
│   ├── db.php             # PDO 数据库封装
│   ├── Cache.php          # 文件缓存类（已加固）
│   ├── header.php         # 公共页眉
│   ├── footer.php         # 公共页脚
│   ├── ip_guard.php       # IP 地域封锁
│   └── ip2region/         # IP 离线库
├── login/                 # 注入练习（登录）
├── board/                 # 胜利榜（黑页）
├── graveyard/             # 战败榜
├── logs/                  # 操作日志（管理员）
├── guestbook/             # 留言板（XSS 练习）
├── upload/                # 文件上传练习
├── register/              # 注册
├── profile/               # 修改密码
├── about/                 # 渗透规则
├── pages/                 # 帮助文档内容
├── H/                     # 黑页存放目录
├── uploads/               # 上传文件目录
├── preferences/           # 缓存目录（禁止 Web 访问）
├── install/
│   ├── install.php        # Web 安装向导
│   └── install.sql        # SQL 安装脚本
└── assets/
    ├── css/style.css      # 样式
    └── js/main.js         # 前端脚本
```

## 免责声明

1. 本项目**仅用于授权的安全教学与演练**，严禁用于非法攻击。
2. 使用者在使用本项目时应遵守当地法律法规，任何违法行为与本项目作者无关。
3. 禁止将本平台作为跳板攻击其他系统。
4. 发现漏洞请在授权范围内测试，勿影响他人使用。

## 更新日志

### v1.1（2026-08-15）

- 修复 PHP 反序列化高危漏洞（Cookie `unserialize` → `json_decode`，Cache 类写入路径限制）
- 修复操作日志未授权访问（改为仅管理员可见）
- 新增全站 CSRF Token 防护
- 新增登录防爆破（15 分钟 5 次限制）
- 新增 Session Cookie 安全标志（HttpOnly / SameSite / Secure）
- 修复 `get_ip()` 信任伪造代理头问题
- 新增 `.htaccess` 安全配置（敏感目录禁止访问、上传目录禁止执行 PHP）
- 新增 `preferences/` 缓存目录
- 所有 PHP 文件添加版权注释头
- 版本号升级至 1.1
- 完善安装脚本（install.php + install.sql）

## License

MIT License

---

**高先生笔记** | [www.gxsnote.cn](https://www.gxsnote.cn) | QQ 67031002
