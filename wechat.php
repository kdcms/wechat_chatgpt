<?php
//下面这行是为了过微信认证的，填写订阅号基本配置的时候需要填URL并验证消息，这时把下面这行取消注释即可。通过验证后可以删掉或继续注释。
//echo $_GET["echostr"];exit;

$xml_tree = simplexml_load_string(file_get_contents("php://input"));
$prompt .= $xml_tree->Content;
$touser = $xml_tree->FromUserName;

$OPENAI_API_KEY = "OPEN_AI_KEY你的KEY";
$ch = curl_init();
$headers  = [
  'Accept: application/json',
  'Content-Type: application/json',
  'Authorization: Bearer ' . $OPENAI_API_KEY . ''
];

$postData = [
  "model" => "gpt-3.5-turbo",
  "temperature" => 0.8,
  "max_tokens" => 2048,
  "top_p" => 1,
  "messages" => [],
];
$postData['messages'][] = ['role' => 'user', 'content' => $prompt];
$postData = json_encode($postData);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$result = curl_exec($ch);
$complete = json_decode($result);

if (isset($complete->choices[0]->message)) {
  $text = trim(str_replace("\\n", "\n", $complete->choices[0]->message->content), "\n");
} elseif (isset($complete->error->message)) {
  $text = "服务器返回错误信息：" . $complete->error->message;
} else {
  $text = "服务器超时或返回异常消息。";
}

?>
<xml>
  <ToUserName>
    <![CDATA[<?= $touser ?>]]>
  </ToUserName>
  <FromUserName>
    <![CDATA[你的公公众号ID]]>
  </FromUserName>
  <CreateTime><?php echo time(); ?></CreateTime>
  <MsgType>
    <![CDATA[text]]>
  </MsgType>
  <Content><![CDATA[<?= $text ?>]]></Content>
  <FuncFlag>0<FuncFlag>
</xml>


