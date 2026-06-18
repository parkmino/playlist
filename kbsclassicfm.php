<?php
// 1. KBS API에서 실시간 스트리밍 주소(m3u8) 동적 추출
$api_url = "https://cfpwwwapi.kbs.co.kr/api/v1/landing/live/channel_code/24";
$json_data = @file_get_contents($api_url);
$stream_url = "";

if (preg_match('/(https:\/\/[^"]+)/', $json_data, $matches)) {
    // 껍데기 따옴표나 이스케이프 문자 제거
    $stream_url = str_replace(['\"', '\\'], '', $matches[1]);
} else {
    $stream_url = "ERROR: URL을 가져올 수 없습니다.";
}

// 현재 이 페이지의 URL 확인
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$pls_url = strtok($current_url, '?') . "?format=pls";

// 2. 만약 뒤에 ?format=pls 가 붙어서 호출되면 순수 PLS 텍스트 파일로 출력
if (isset($_GET['format']) && $_GET['format'] === 'pls') {
    header("Content-Type: audio/x-scpls; charset=utf-8");
    header("Content-Disposition: inline; filename=kbs1fm.pls");
    
    echo "[playlist]\n";
    echo "File1=" . $stream_url . "\n";
    echo "Title1=KBS 1FM (Classic FM)\n";
    echo "Length1=-1\n";
    echo "NumberOfEntries=1\n";
    echo "Version=2\n";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KBS 1FM 실시간 플레이어 & PLS 제공</title>
    <!-- 웹 브라우저 m3u8 재생을 위한 HLS.js 라이브러리 -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 40px 20px; display: flex; justify-content: center; }
        .container { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 500px; width: 100%; text-align: center; }
        h1 { font-size: 24px; color: #0056b3; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 25px; }
        audio { width: 100%; margin: 20px 0; }
        .btn-box { margin-top: 25px; display: flex; flex-direction: column; gap: 10px; }
        .btn { display: block; padding: 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.2s; }
        .btn:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 6px; font-size: 12px; text-align: left; margin-top: 25px; word-break: break-all; border-left: 4px solid #007bff; }
    </style>
</head>
<body>

<div class="container">
    <h1>📻 KBS 1FM 실시간 웹 플레이어</h1>
    <div class="subtitle">Classic FM / 국악 방송</div>

    <!-- 웹 브라우저용 HTML5 오디오 태그 -->
    <audio id="audio" controls autoplay></audio>

    <div class="btn-box">
        <!-- 자기 자신에게 ?format=pls를 붙여서 다운로드 링크로 활용 -->
        <a href="<?php echo $pls_url; ?>" class="btn">⬇️ PLS 파일 다운로드 (PC 플레이어용)</a>
    </div>

    <div class="info-box">
        <strong>💡 외부 플레이어(VLC, 팟플레이어 등) 주소 추가용 URL:</strong><br>
        <span style="color:#d9534f; font-weight:bold;"><?php echo $pls_url; ?></span>
        <br><br>
        <strong>🔗 현재 추출된 원본 주소 (m3u8):</strong><br>
        <span style="color:#555;"><?php echo $stream_url; ?></span>
    </div>
</div>

<script>
    // HLS.js를 사용하여 웹 브라우저에서 KBS m3u8 스트리밍 재생
    const audio = document.getElementById('audio');
    const streamUrl = "<?php echo $stream_url; ?>";

    if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(streamUrl);
        hls.attachMedia(audio);
    } else if (audio.canPlayType('application/vnd.apple.mpegurl')) {
        audio.src = streamUrl;
    }
</script>

</body>
</html>