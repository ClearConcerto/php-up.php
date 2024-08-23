文件上传和管理示例

本项目提供了一个简单的 PHP 文件上传和管理示例，包括文件上传、分块上传、文件下载、文件合并和删除操作。
功能概述

文件上传：支持多文件上传并显示上传进度。
分块上传：支持大文件分块上传，自动处理分块合并。
文件下载：支持从远程 URL 下载文件。
文件删除：允许删除服务器上的文件。
文件列表：显示上传目录内的文件列表及其信息（名称、最后修改时间、大小）。

环境要求

PHP 7.0 或更高版本
cURL 扩展（用于远程文件下载）
支持写入的服务器目录（用于文件上传和存储）

使用说明

文件上传

在页面上选择要上传的文件。
点击“上传文件”按钮开始上传。
上传进度会显示在页面上。

分块上传

如果上传的文件过大，会自动进行分块上传，并在上传完成后进行合并。
文件下载

使用 ?getfile= 参数指定远程文件的 URL。
系统会验证文件类型和大小，下载并保存到服务器指定目录。

文件删除

点击文件列表中的“Delete”链接可以删除文件。
删除后，页面将自动刷新以更新文件列表。

文件列表

页面将显示当前上传目录中的所有文件，包括：
文件名称
最后修改时间
文件大小

代码结构

up.php: 主 PHP 文件，处理文件上传、下载、删除和分块合并等功能。
uploads/: 文件上传目录。

安全性

请注意：
代码中包含了允许上传 PHP 文件的设置。请根据实际需要修改 allowedExtensions 数组以限制上传的文件类型。
远程文件下载使用了固定的用户代理和 Cookie，可能需要根据实际情况调整。

免责声明

请确保在合适的环境下使用本代码，避免在生产环境中使用未经充分测试的代码。



许可证

本项目采用 MIT 许可证 开源。
