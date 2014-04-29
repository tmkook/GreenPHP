<?php
/**
*--------------------------------------------------------------
* 分页类
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
* 分页类只返回页码不返回HTML
#--------------------------------------------------------------
$p = new Paging(10000,$_GET['page']);
echo implode(',',$p->getGoing()); //往后页码
echo '('.$p->getShow().')'; //当前页
echo implode(',',$p->getBack()); //往前页码
echo '<br>';
echo '<a href="?page='.$p->getPrev().'">上一页</a>';
echo '<a href="?page='.$p->getNext().'">下一页</a>';
---------------------------------------------------------------#
*/
class Paging
{
	protected $_total = 0;  //总条数
	protected $_show  = 1;  //当前页
	protected $_size  = 10; //页码数
	protected $_rows  = 1;  //每页显示条数
	protected $_first = 1;  //首页
	protected $_last  = 1;  //尾页
	protected $_back  = array(); //往首页靠近
	protected $_going = array(); //往尾页靠近

   /**
    * 分页初始化
    *
    * @parame integer $total 总条数
    * @parame integer $show  当前页
    * @parame integer $size  页码数
    * @parame integer $page_half 前后页码数
    *
    */
    public function __construct($total, $show, $rows=10, $size=10){
        $this->_total = $total;
		$this->_show  = $show <= 0? 1 : $show;
		$this->_rows  = $rows;
        $this->_size  = $size;
        $this->_last  = ceil($this->_total / $this->_rows);
		$this->_last  = $this->_last <= 0? 1 : $this->_last;
		$page_half = ceil($size / 2);
		$show = $show - $page_half -1;
		$i = $page_half;
		while($i--){
			$show += 1;
			if($show > 0){
				$this->_back[] = $show;
			}else{
				$page_half += 1;
			}
		}
		
		$show = $this->_show;
		while($page_half--){
			$show++;
			if($show > $this->_last){
				break;
			}else{
				$this->_going[] = $show;
			}
		}
    }

   /**
    * 获取Limit数
    *
    * @parame integer $page 页码
    * @return integer
    */
    public function getLimit(){
		return array(
			($this->_show - 1) * $this->_rows,
			$this->_rows
		);
    }
    
   /**
    * 获取向后的页码
    *
    * @return array
    */
    public function getGoing(){
        return $this->_going;
    }
    
   /**
    * 获取向前的页码
    *
    * @return array
    */
    public function getBack(){
        return $this->_back;
    }
    
   /**
    * 获取当前页
    *
    * @return integer
    */
    public function getShow(){
        return $this->_show;
    }
    
   /**
    * 获取上一页
    *
    * @return integer
    */
    public function getPrev(){
		$prev = $this->_show-1;
		if($prev < $this->_first){
			$prev = $this->_first;
		}
		return $prev;
    }

   /**
    * 获取下一页
    *
    * @return integer
    */
    public function getNext(){
		$next = $this->_show+1;
		if($next > $this->_last){
			$next = $this->_last;
		}
		return $next;
    }
}
