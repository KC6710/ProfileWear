var autocomplete = {
	template: '#autocomplete-template',
	props:['index', 'token'],
	data: function(){
		return{
			value:'',
			// token: ''
		}
	},
	methods:{
		updateValue:function(){
			this.$emit('input',this.value);

			// apply to parent component
			this.$emit('changed',{
				'index': this.index,
				'value': this.value,
			});
		}
	},
	mounted:function(){
		/*
    url = window.location.href;
    name = 'token';
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    this.token = decodeURIComponent(results[2].replace(/\+/g, " "));
		*/
    var _self = this;
    $(this.$refs['input']).autocomplete({
        'source': function (request, response) {
            $.ajax({
                url: 'index.php?route=codevoc/b2bmanager_inventory.spcountautocomplete&user_token='+_self.token+'&filter_name=' + encodeURIComponent(request),
                dataType: 'json',
                success: function (json) {
                    response($.map(json, function (item) {
                        return {
                            label: item['name'],
                            value: item['product_id'],
                            manufacturer: item['manufacturer'],
                            product_name: item['product_name'],
                            model: item['model']
                        }
                    }));
                }
            });
        },
        'select': function (item) {
            var model = item['model'];
            var product_name = item['product_name'];
            var manufecturer = item['manufacturer'];
            $(this).val(model);
            _self.value =model;
            _self.$emit('changed',{
            	'index': _self.index,
            	'value': _self.value,
            	'product_name' : product_name,
            	'manufecturer' : manufecturer
            });
        }
    });
	}
};

/*
 * Add article component
*/
var addArticleComponent = {
	components:{
		'autocomplete':autocomplete
	},
	template: '#add-article-component-template',
	props: ['show','token'],
	delimiters: ['${', '}'],
	data:function(){
		return{
			isSuccess:false,
			isError:false,
			infoMessage:[],
			items:[],
			itemErrors:[]
		}
	},
	mounted:function(){

	},
	watch:{
		show:function(newShow){
			if(newShow == true){
				this.initItems();
			}
		}
	},
	computed:{
		isMessage:function(){
			return this.isSuccess == true || this.isError == true;
		}
	},
	methods:{
		initItems:function(){
			this.items = [];
			this.itemErrors = [];
			for(i=0;i<10;i++){
				this.items.push({
					article_nr : '',
					name : '',
					color : '',
					size : '',
					brand : '',
					quantity : 1,
					location : '',
					price_1 : 0,
					price_2 : 0,
					comments : ''
				});
			}
		},
		close:function(){
			this.isError = false;
			this.isSuccess = false;
			this.infoMessage = [];
			this.items = [];
			this.itemErrors = [];
			this.$emit('close-add-article-dialog');
		},
		save:function(event){
			var result = {
				items: this.items
			};
			this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_add_article&user_token='+this.token,result,{
				emulateJSON : true,
			}).then(function(response){
				if(response.body.result.success){
					this.isSuccess = true;
					this.isError = false;
					this.infoMessage = response.body.result.success;
					this.initItems();
					this.$emit("update-items");
				}else if(response.body.result.error){
					this.isSuccess = false;
					this.isError = true;
					if(response.body.result.error.items){
						this.itemErrors = response.body.result.error.items;
						this.infoMessage = ["Solve following errors"];
					}else{
						this.infoMessage = response.body.result.error;
					}
				}
			},function(response){

			});
			return false;
		},
		autoCompleteChanged:function(event){
			this.items[event.index].article_nr = event.value;
			this.items[event.index].name = event.product_name;
			this.items[event.index].brand = event.manufecturer;
		}
	}
};

/*
 * Edit component
*/
var editComponent = {
	components:{
		'autocomplete':autocomplete
	},
	template: '#edit-component-template',
	delimiters: ['${', '}'],
	props: ['show','item','token'],
	data:function(){
		return{
			isSuccess:false,
			isError:false,
			infoMessage:[],
			currentItem:{
				article_nr : '',
				name : '',
				color : '',
				size : '',
				brand : '',
				quantity : '',
				location : '',
				price_1 : '',
				price_2 : '',
				comments : ''
			}
		}
	},
	computed:{
		isMessage:function(){
			return this.isSuccess == true || this.isError == true;
		}
	},
	watch:{
		show:function(newShow){
			if(newShow == true){
				this.currentItem.article_nr = this.item.article_nr;
				this.currentItem.name = this.item.name;
				this.currentItem.color = this.item.color;
				this.currentItem.size = this.item.size;
				this.currentItem.brand = this.item.brand;
				this.currentItem.quantity = this.item.quantity;
				this.currentItem.location = this.item.location;
				this.currentItem.price_1 = this.item.price_1;
				this.currentItem.price_2 = this.item.price_2;
				this.currentItem.comments = this.item.comments;
			}
		}
	},
	methods:{
		close:function(){
			this.isError = false;
			this.isSuccess = false;
			this.infoMessage = [];
			this.$emit('close-edit-dialog');
		},
		save:function(){
			var object = {};
			$.each($(this.$refs.edit_form).serializeArray(), function(_, kv) {
			  if (object.hasOwnProperty(kv.name)) {
			    object[kv.name] = $.makeArray(object[kv.name]);
			    object[kv.name].push(kv.value);
			  }
			  else {
			    object[kv.name] = kv.value;
			  }
			});

			this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_update_article&user_token='+this.token,object,{
				emulateJSON : true,
			}).then(function(response){
				if(response.body.result.success){
					this.isSuccess = true;
					this.isError = false;
					this.infoMessage = response.body.result.success;
					this.$emit("update-items");
				}else if(response.body.result.error){
					this.isSuccess = false;
					this.isError = true;
					this.infoMessage = response.body.result.error;
				}
			},function(response){

			});
			return false;
		},
		autoCompleteChanged:function(event){
			this.currentItem.article_nr = event.value;
		}
	}
};

/*
 * Add to list component
*/
var addToListComponent = {
	template: '#add-to-list-component-template',
	delimiters: ['${', '}'],
	props: ['show','item','token'],
	data:function(){
		return{
			isSuccess:false,
			isError:false,
			infoMessage:[],
			quantity: 1
		}
	},
	computed:{
		isMessage:function(){
			return this.isSuccess == true || this.isError == true;
		}
	},
	methods:{
		close:function(){
			this.isError = false;
			this.isSuccess = false;
			this.infoMessage = [];
			this.$emit('close-add-to-list-dialog');
		},
		save:function(){
			// restrict user to add more quantity than available
			if(parseInt(this.quantity) > parseInt(this.item.quantity)){
				alert("Entered quantity exceeds than available product quantity");
				this.quantity = 1;
				return false;
			}

			var object = {
				article_nr : this.item.article_nr,
				item_id : this.item.id,
				quantity: this.quantity
			};
			this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_add_to_list&user_token='+this.token,object,{
				emulateJSON : true,
			}).then(function(response){
				if(response.body.result.success){
					this.isSuccess = true;
					this.isError = false;
					this.quantity = 1;
					this.infoMessage = response.body.result.success;
					this.$emit("update-list");
					this.$emit('close-add-to-list-dialog');
				}else if(response.body.result.error){
					this.isSuccess = false;
					this.isError = true;
					this.infoMessage = response.body.result.error;
				}
			},function(response){

			});
		}
	}
};

/*
 * Add to list table component
*/
var addToListTableComponent = {
	template: '#add-to-list-table-component-template',
	delimiters: ['${', '}'],
	props: ['show','token','items'],
	data:function(){
		return{
			isSuccess:false,
			isError:false,
			infoMessage:[]
		}
	},
	computed:{
		isMessage:function(){
			return this.isSuccess == true || this.isError == true;
		},
		listTotal:function(){
			var totalItems = 0;
			for (var i = 0; i < this.items.length; i++) {
				totalItems += parseInt(this.items[i].quantity);
			}
			return totalItems;
		}
	},
	methods:{
		close:function(){
			this.isError = false;
			this.isSuccess = false;
			this.infoMessage = [];
			this.$emit('close-add-to-list-table-dialog');
		},
		deleteItem:function(item,event){
			event.preventDefault();
			var object = {
				id : item.id
			};
			this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_delete_from_list&user_token='+this.token,object,{
				emulateJSON : true,
			}).then(function(response){
				if(response.body.result.success){
					this.isSuccess = true;
					this.isError = false;
					this.quantity = 1;
					this.infoMessage = response.body.result.success;
					this.$emit("update-list");
				}else if(response.body.result.error){
					this.isSuccess = false;
					this.isError = true;
					this.infoMessage = response.body.result.error;
				}
			},function(response){

			});

			return false;
		},
		deleteAll:function(event){
			event.preventDefault();
			this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_delete_all_from_list&user_token='+this.token,{
				emulateJSON : true,
			}).then(function(response){
				if(response.body.result.success){
					this.isSuccess = true;
					this.isError = false;
					this.quantity = 1;
					this.infoMessage = response.body.result.success;
					this.$emit("update-list");
				}else if(response.body.result.error){
					this.isSuccess = false;
					this.isError = true;
					this.infoMessage = response.body.result.error;
				}
			},function(response){

			});

			return false;
		},
		openPrint:function(){
			var url = 'index.php?route=codevoc/b2bmanager_inventory.print_view&user_token='+this.token;
			window.open(url,"","height=500,width=800");
		},
		collectAndClear:function(){
			event.preventDefault();
			this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_collect_and_clear&user_token='+this.token,{
				emulateJSON : true,
			}).then(function(response){
				if(response.body.result.success){
					this.isSuccess = true;
					this.isError = false;
					this.quantity = 1;
					this.infoMessage = response.body.result.success;
					this.$emit("update-list");
					this.$emit("update-items");
				}else if(response.body.result.error){
					this.isSuccess = false;
					this.isError = true;
					this.infoMessage = response.body.result.error;
				}
			},function(response){

			});

			return false;
		}
	}
};

var app = new Vue({
	delimiters: ['${', '}'],
	components:{
		'edit-component': editComponent,
		'add-to-list-component': addToListComponent,
		'add-to-list-table-component': addToListTableComponent,
		'add-article-component': addArticleComponent
	},
	el: '.spwcountinventory_wrapper',
	props:['closeEditDialog'],
	data: {
		items: [],
		addToListItems: [],
		filter_article_nr: '',
		filter_name: '',
		filter_color: '',
		filter_size: '',
		filter_brand: '',
		filter_quantity: '',
		filter_location: '',
		token: '',
		showEditDialog: false,
		currentEditItem: null,
		showAddToListDialog: false,
		currentAddToListItem: null,
		showAddToListTableDialog: false,
		showAddArticleDialog:false,
		animateShowListButton:false
	},
	mounted(){
		var token = ($(this.$el).attr("token"));
		this.token = token;
		this.getData();
	},
	methods:{
		getData : function(){
			this.$http.get('index.php?route=codevoc/b2bmanager_inventory.api_get_list&user_token='+this.token).then(function(response){
				// success
				this.items = response.body.items;
				this.addToListItems = response.body.add_to_list_items;
			},function(response){
				// error
			});
		},
		deleteItem:function(item,event){
			event.preventDefault();
			if(confirm("Are you sure to delete?")){
				var id = item.id;
				if(id){
					var formData = new FormData();
				  // append string
				  formData.append('id', id);
					this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_delete_item&user_token='+this.token,formData).then(function(response){
						// success
						alert(response.body.result.success);
						this.getData();
					},function(response){
						// error
						alert("Unable to delete item.");
					});
				}
			}
			return false;
		},
		copyItem:function(item,event){
			event.preventDefault();
			if(confirm("Are you sure to copy?")){
				var id = item.id;
				if(id){
					var formData = new FormData();
				  // append string
				  formData.append('id', id);
					this.$http.post('index.php?route=codevoc/b2bmanager_inventory.api_copy_item&user_token='+this.token,formData).then(function(response){
						// success
						alert(response.body.result.success);
						this.getData();
					},function(response){
						// error
						alert("Unable to copy item.");
					});
				}
			}
			return false;
		},
		editItem:function(item,event){
			event.preventDefault();
			this.showEditDialog = true;
			this.currentEditItem = item;
			return false;
		},
		closeEdit:function(){
			this.showEditDialog = false;
		},
		closeAddToList:function(){
			this.showAddToListDialog = false;
			this.jumpShowListButton();
		},
		closeAddToListTable:function(){
			this.showAddToListTableDialog = false;
		},
		addToList:function(item,event){
			event.preventDefault();
			this.showAddToListDialog = true;
			this.currentAddToListItem = item;
			return false;
		},
		showAddToList:function(event){
			event.preventDefault();
			this.showAddToListTableDialog = true;
			return false;
		},
		updateAddToList:function(){
			this.$http.get('index.php?route=codevoc/b2bmanager_inventory.api_get_add_to_list&user_token='+this.token).then(function(response){
				// success
				this.addToListItems = response.body.add_to_list_items;
			},function(response){
				// error
			});
			return false;
		},
		showAddArticle:function(event){
			event.preventDefault();
			this.showAddArticleDialog = true;
			return false;
		},
		closeAddArticle:function(){
			this.showAddArticleDialog = false;
		},
		jumpShowListButton:function(){
			this.animateShowListButton = true;
			var _self = this;
			setTimeout(function(){
				_self.animateShowListButton = false;
			}, 2000);
		}
	},
	computed:{
		filteredItems: function(){
			var self = this;
			return this.items.filter(function (item) {
        return item.article_nr.toLowerCase().indexOf(self.filter_article_nr.toLowerCase()) !== -1 && item.name.toLowerCase().indexOf(self.filter_name.toLowerCase()) !== -1 && item.color.toLowerCase().indexOf(self.filter_color.toLowerCase()) !== -1 &&  item.size.toLowerCase().indexOf(self.filter_size.toLowerCase()) !== -1 &&  item.size.toLowerCase().indexOf(self.filter_size.toLowerCase()) !== -1 &&  item.brand.toLowerCase().indexOf(self.filter_brand.toLowerCase()) !== -1 &&  item.quantity.toLowerCase().indexOf(self.filter_quantity.toLowerCase()) !== -1 &&  item.location.toLowerCase().indexOf(self.filter_location.toLowerCase()) !== -1
			})
			/*
			.filter(function (item) {
        //return item.name.toLowerCase().indexOf(self.filter_name.toLowerCase()) !== -1
      }).filter(function (item) {
        //return item.color.toLowerCase().indexOf(self.filter_color.toLowerCase()) !== -1
      }).filter(function (item) {
        //return item.size.toLowerCase().indexOf(self.filter_size.toLowerCase()) !== -1
      }).filter(function (item) {
        //return item.brand.toLowerCase().indexOf(self.filter_brand.toLowerCase()) !== -1
      }).filter(function (item) {
        //return item.quantity.toLowerCase().indexOf(self.filter_quantity.toLowerCase()) !== -1
      }).filter(function (item) {
        //return item.location.toLowerCase().indexOf(self.filter_location.toLowerCase()) !== -1
      })*/
		}
	}
});