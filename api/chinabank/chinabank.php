<!--  
 * ====================================================================
 *
 *                Send.php ���������߼���֧���ṩ
 *
 *  ��ҳ�����������ҳ���ж�����Ϣ,���ύ֧������Ϣ����������֧��ƽ̨....
 *
 * 
 * ====================================================================
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<title>����֧���ӿ�PHP��</title>

<link href="css/index.css" rel="stylesheet" type="text/css">
</head>
<body  onLoad="javascript:document.E_FORM.submit()">
<?php
error_reporting(E_ALL ^E_NOTICE);
//****************************************
	$v_mid = '1001';								    // 1001���������ߵĲ����̻��ţ��̻�Ҫ�滻Ϊ�Լ����̻��š�

	$v_url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?m=recharge_chinabank&a=return';	// �̻��Զ��巵�ؽ���֧�������ҳ�档��ӦReceive.phpʾ����
	                                                    //����"��������֧��B2Cϵͳ�̻��ӿ��ĵ�v4.1.doc"��2.3.3.1
	
	$key   = 'test';								    // ����"��������֧��B2Cϵͳ�̻��ӿ��ĵ�v4.1.doc"��2.4.1�������á�

	$remark2 = '[url:=http://'.$_SERVER['HTTP_HOST'].'/index.php?m=recharge_chinabank&a=Notify]'; //�������첽֪ͨ�Ľ��յ�ַ����ӦAutoReceive.phpʾ��������Ҫ��[url:=]��ʽ��
																//����"��������֧��B2Cϵͳ�̻��ӿ��ĵ�v4.1.doc"��2.3.3.2��

//****************************************


if(trim($_REQUEST['orderno'])<>"")					//�ж��Ƿ��д��ݶ�����
{
	   $v_oid = trim($_REQUEST['orderno']); 
}
else
{
	   $v_oid = date('Ymd',time())."-".$v_mid."-".date('His',time());//�����ţ����鹹�ɸ�ʽ ������-�̻���-Сʱ������

}
	 
	$v_amount = trim($_REQUEST['order_price']);                   //֧�����                 
    $v_moneytype = "CNY";                                            //����

	$text = $v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key;        //md5����ƴ�մ�,ע��˳���ܱ�
    $v_md5info = strtoupper(md5($text));                             //md5�������ܲ�ת���ɴ�д��ĸ

	 $remark1 = trim($_REQUEST['order_info']);					 //��ע�ֶ�1
	 



	$v_rcvname   = trim($_REQUEST['v_rcvname'])  ;		// �ջ���
	$v_rcvaddr   = trim($_REQUEST['v_rcvaddr'])  ;		// �ջ���ַ
	$v_rcvtel    = trim($_REQUEST['v_rcvtel'])   ;		// �ջ��˵绰
	$v_rcvpost   = trim($_REQUEST['v_rcvpost'])  ;		// �ջ����ʱ�
	$v_rcvemail  = trim($_REQUEST['v_rcvemail']) ;		// �ջ����ʼ�
	$v_rcvmobile = trim($_REQUEST['v_rcvmobile']);		// �ջ����ֻ���

	$v_ordername   = trim($_REQUEST['v_ordername'])  ;	// ����������
	$v_orderaddr   = trim($_REQUEST['v_orderaddr'])  ;	// �����˵�ַ
	$v_ordertel    = trim($_REQUEST['v_ordertel'])   ;	// �����˵绰
	$v_orderpost   = trim($_REQUEST['v_orderpost'])  ;	// �������ʱ�
	$v_orderemail  = trim($_REQUEST['v_orderemail']) ;	// �������ʼ�
	$v_ordermobile = trim($_REQUEST['v_ordermobile']);	// �������ֻ��� 

?>

<!--������ϢΪ��׼�� HTML ��ʽ + PHP ���� ƴ�ն��ɵ� �������� ֧���ӿڱ�׼��ʾҳ�� �����޸�-->

<form method="post" name="E_FORM" action="https://Pay3.chinabank.com.cn/PayGate">
	<input type="hidden" name="v_mid"         value="<?php echo $v_mid;?>">
	<input type="hidden" name="v_oid"         value="<?php echo $v_oid;?>">
	<input type="hidden" name="v_amount"      value="<?php echo $v_amount;?>">
	<input type="hidden" name="v_moneytype"   value="<?php echo $v_moneytype;?>">
	<input type="hidden" name="v_url"         value="<?php echo $v_url;?>">
	<input type="hidden" name="v_md5info"     value="<?php echo $v_md5info;?>">
 
 <!--���¼�����Ϊ����֧����ɺ���֧��������Ϣһͬ������Ϣ����ҳ -->	
	
	<input type="hidden" name="remark1"       value="<?php echo $remark1;?>">
	<input type="hidden" name="remark2"       value="<?php echo $remark2;?>">



<!--���¼���ֻ��������¼�ͻ���Ϣ�����Բ��ã���Ӱ��֧�� -->
	<input type="hidden" name="v_rcvname"      value="<?php echo $v_rcvname;?>">
	<input type="hidden" name="v_rcvtel"       value="<?php echo $v_rcvtel;?>">
	<input type="hidden" name="v_rcvpost"      value="<?php echo $v_rcvpost;?>">
	<input type="hidden" name="v_rcvaddr"      value="<?php echo $v_rcvaddr;?>">
	<input type="hidden" name="v_rcvemail"     value="<?php echo $v_rcvemail;?>">
	<input type="hidden" name="v_rcvmobile"    value="<?php echo $v_rcvmobile;?>">

	<input type="hidden" name="v_ordername"    value="<?php echo $v_ordername;?>">
	<input type="hidden" name="v_ordertel"     value="<?php echo $v_ordertel;?>">
	<input type="hidden" name="v_orderpost"    value="<?php echo $v_orderpost;?>">
	<input type="hidden" name="v_orderaddr"    value="<?php echo $v_orderaddr;?>">
	<input type="hidden" name="v_ordermobile"  value="<?php echo $v_ordermobile;?>">
	<input type="hidden" name="v_orderemail"   value="<?php echo $v_orderemail;?>">

</form>

</body>
</html>
