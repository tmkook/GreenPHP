$.fn.SearchTools = function(obj,isSearch){
	if(typeof(isSearch) == 'undefined') isSearch = true;
	var defaults = {
		container:'.search_box',
		selects:'.search_item',
		add:'.search_add',
		del:'.search_del',
		fieldname:'field[]',
		wdname:'wd[]'
	};
	obj = $.extend(defaults,obj);

	this.items = function(){
		$(obj.container).on('click',obj.add,function(){
			var items = $(obj.selects+':first').clone();
			$(obj.container).append(items);
			$(items).find('input').val('');
		});
		
		$(obj.container).on('click',obj.del,function(){
			if($(obj.container).find(obj.selects).length > 1)
			$(this).parent().remove();
		});
	};

	this.search = function (){
		var that = this;
		//获取get参数
		this.getQueryString = function (name){
			var uri = decodeURI(window.location.search.substr(1)).split('&');
			var param = [];
			for(var i in uri){
				var r = uri[i].split('=');
				param[r[0]]? param[r[0]].push(r[1]) : param[r[0]] = [r[1]];
			}
			if(param[name]) return param[name];
			return '';
		}
		
		//根据URI生成select查询表单
		this.buildQuerySelectes = function(){
			var fields = that.getQueryString(obj.fieldname);
			var wds = that.getQueryString(obj.wdname);
			if(fields.length > 0 && wds.length > 0){
				for(var i in fields){
					that.addRowSelect(wds[i],fields[i],i);
				}
			}
		}
	
		//增加一行搜索条件
		this.addRowSelect = function (wd,selected,bdid){
			if(typeof(wd) == undefined) wd = null;
			if(typeof(selected) == undefined) selected = null;
			if(typeof(bdid) == undefined) bdid = null;
			if(bdid == 0){
				var items = $(obj.selects+':first');
			}else{
				var items = $(obj.selects+':first').clone();
				$(obj.container).append(items);
			}
			$(items).find('option').each(function(index, element) {
				if($(this).val() == selected){
					 $(this).attr("selected", "selected");
				}
			});
			$(items).find("input[name='"+obj.wdname+"']").val(wd);
		}
	
		//删除一行搜索条件
		this.delRowSelect = function (del){
			if($(obj.container).find(obj.selects).length > 1)
			$(del).parent().remove();
		}
		
		//构造方法初始化
		this.buildQuerySelectes();
		
		$(obj.container).on('click',obj.add,function(){
			that.addRowSelect();
		});
		
		$(obj.container).on('click',obj.del,function(){
			that.delRowSelect(this);
		});
	};
	return isSearch? new this.search() : new this.items();
}