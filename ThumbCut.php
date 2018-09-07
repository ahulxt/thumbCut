<?php

namespace Ahulxt;
/**
 * 图片裁剪，生成缩略图
 * 默认为不变成
 * 默认存储到本包的storage目录
 * 生成的图片名称为原图名称 + '_thumb_' + 时间戳 + 随机字符串
 * 参数示例：
 * $data = [
		'source'       => 'D:\Documents\Pictures\gamelogo\599823e78008c.png',
		'width'        => 256,
		'height'       => 256,
		'fixed'        => 1,
		'output'       => true,
		'storage'      => true,
		'storage_path' => 'C:\Users\xxx\Desktop/',
	];
 */
class ThumbCut {
	
	/**
	 * @param array $data
	 * @author 李小同
	 * @date   2018-09-07 10:13:14
	 * @return array
	 */
	public function thumb(array $data = []) {
		
		$source = $data['source'];
		$width  = $data['width'];
		$height = $data['height'];
		
		if (file_exists($source) && $sizeInfo = getimagesize($source)) {
			
			$srcW = $sizeInfo[0];
			$srcH = $sizeInfo[1];
			$mime = substr($sizeInfo['mime'], strlen('image/'));
			
			$rateSrc = $srcW / $srcH; # 原始的宽高比
			$rateDst = $width / $height; # 缩略图的宽高比
			
			$imageCreateFunc = 'imagecreatefrom'.$mime;
			$srcImage        = $imageCreateFunc($source);
			imagesavealpha($srcImage, true); # 不要丢了$source图像的透明色
			
			$dstImage = imagecreatetruecolor($width, $height);
			imagealphablending($dstImage, false); # 不合并颜色,直接用$img图像颜色替换,包括透明色
			imagesavealpha($dstImage, true); # 不要丢了$dst图像的透明色
			
			if ($rateSrc >= $rateDst) { # 原图是偏宽的
				
				# 计算出撑大后的临时图的像素，用于计算裁切的点
				$tmpH = $srcH;
				$tmpW = $rateDst * $tmpH;
				
				$srcX = ($srcW - $tmpW) / 2;
				$srcY = 0;
				
			} else {
				
				# 计算出撑大后的临时图的像素，用于计算裁切的点
				$tmpW = $srcW;
				$tmpH = $rateDst * $tmpW;
				
				$srcX = 0;
				$srcY = ($srcH - $tmpH) / 2;
			}
			
			# 不固定形状
			if (!isset($data['fixed']) || $data['fixed']) {
				$srcX = 0;
				$srcY = 0;
				$tmpW = $srcW;
				$tmpH = $srcH;
			}
			
			# 裁剪 & 缩放
			imagecopyresampled($dstImage, $srcImage, 0, 0, $srcX, $srcY, $width, $height, $tmpW, $tmpH);
			
			$imageFunc = 'image'.$mime;
			if (!empty($data['output'])) {
				header('Content-type:'.$sizeInfo['mime']);
				$imageFunc($dstImage);
			}
			
			if (!isset($data['storage']) || !empty($data['storage'])) {
				if (empty($data['storage_path'])) $data['storage_path'] = __DIR__.'/storage';
				if (!is_dir($data['storage_path'])) mkdir($data['storage_path'], 777);
				
				$fileName = basename($source);
				$fileType = strrchr($fileName, '.');
				$fileName = substr($fileName, 0, -(strlen($fileType)));
				$dstName  = $fileName.'_thumb_'.time().uniqid().'.'.$mime;
				$dstPath  = $data['storage_path'].'/'.$dstName;
				$imageFunc($dstImage, $dstPath);
			}
			
			imagedestroy($srcImage);
			imagedestroy($dstImage);
			
			return ['dst_name' => $dstName, 'dst_path' => $dstPath];
			
		} else {
			return ['error' => 'invalid image'];
		}
	}
}