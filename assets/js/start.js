require('../css/start.css');

(function(){

  document.addEventListener("DOMContentLoaded", DOMReady);

  function DOMReady() {
    $todoNS = window['$todo'] || (window['$todo'] = {});
    $todoNS['selectors'] = {};
    $todoNS['selectors']['leftmenu'] = document.querySelector('#js-cat-menu');
    $todoNS['selectors']['todolist'] = document.querySelector('#js-todos-list');

    $todoNS.selectors.leftmenu.addEventListener('click', onLeftMenuClick);
    new $todoNS.TodoEdit();
    document.querySelector('#js_new_todo').addEventListener('click', function(e){
      e.preventDefault();
      let ajax = new $todoNS.$Ajax();
      let url = this.getAttribute("href");
      let modal = UIkit.modal("#js-edit-modal");
      ajax.send({
        url: url,
        success: function(data) {
          modal.dialog[0].getElementsByClassName('uk-modal-content')[0].innerHTML = data;
          modal.dialog[0].getElementsByClassName('uk-modal-spinner')[0].style.display = 'none';
          let form = document.forms.appbundle_todos;
          form.getElementsByClassName('submit')[0].addEventListener('click', function(e){
            e.preventDefault();
            var formData = new FormData(form);
            ajax.send({
              url: url,
              data:formData,
              success: function(data) {
                let parsedData = JSON.parse(data);
                UIkit.modal.alert("Saved!");
                let _parseData = new $todoNS.$TodoParse({
                  data: parsedData.category.todos
                });
                _parseData.parse();
              }
            });
            return false;
          });
          modal.on(
            {
              'show.uk.modal': function(){
              },

              'hide.uk.modal': function(){
                modal.dialog[0].getElementsByClassName('uk-modal-content')[0].innerHTML = '';
                modal.dialog[0].getElementsByClassName('uk-modal-spinner')[0].style.display = 'block';
              }
          });
        }
      })
    });
  }

  function onLeftMenuClick(e) {
    let trigger = e.target;
    e.preventDefault();
    if(e.target.nodeName === "A") {
      let url = e.target.getAttribute("href");
      let ajax = new $todo.$Ajax();
      ajax.send({
        url: url,
        success: function(data) {
          let parseData = new $todoNS.$TodoParse({
            data: JSON.parse(data)
          });
          parseData.parse();
          let allLi = trigger.closest("ul").getElementsByTagName('a');
          for(let i=0;i<allLi.length;i++) {
            allLi[i].classList.remove('uk-text-success');
            allLi[i].classList.remove('selected');
          }
          trigger.classList.add("uk-text-success");
          trigger.classList.add("selected");
          window.history.pushState('', '', url);
        }
      });
    }
    return false;
  }

}());

/**
 * Todo item action
 * *****************************************************************************
 */
(function (){
  $todoNS = window['$todo'] || (window['$todo'] = {});

  let TodoEdit = function(params) {
    let $this = this;
    let defaults = {
      btnGroup: '.uk-button-group'
    };
    $this.cfg = Object.assign(defaults, params);
    $this.init();
  }

  TodoEdit.prototype.init = function() {
    let $this = this;
    let todoList = $todo.selectors.todolist;
    todoList.addEventListener('click', function(e){
      _eventProcess(e,$this)
    });
  }

  TodoEdit.prototype.edit = function(e) {
    let selectedCat = $todo.selectors.leftmenu.getElementsByClassName('selected')[0] || 'show_all';
    let url = e.target.getAttribute("href");
    let modal = document.querySelector('#js-edit-modal');
    let m = UIkit.modal("#js-edit-modal");
    let ajax = new $todo.$Ajax();
    ajax.send({
      url: url,
      success: function(data) {
        modal.getElementsByClassName('uk-modal-content')[0].innerHTML = data;
        modal.getElementsByClassName('uk-modal-spinner')[0].style.display = 'none';
        let form = document.forms.appbundle_todos;
        form.getElementsByClassName('submit')[0].addEventListener('click', function(e){
          e.preventDefault();
          var formData = new FormData(form);
          ajax.send({
            url: (selectedCat === 'show_all') ? url:selectedCat.getAttribute('href'),
            data:formData,
            success: function(data) {
              let parsedData = JSON.parse(data);
              if(!parsedData.error) {
                UIkit.modal.alert("Saved!");
                let errors = form.getElementsByClassName('error');
                for(let i = 0; i < errors.length;i++) {
                  errors[i].classList.remove('error');
                }
                let _parseData = new $todoNS.$TodoParse({
                  data: (parsedData.category)?parsedData.category.todos:parsedData
                });
                _parseData.parse();
              } else {
                let _parseErrors = new $todoNS.$TodoParse({
                  data: parsedData.error
                });
                _parseErrors.errors(form);
              }
            }
          });
          return false;
        });
      }
    });
    m.on(
      {
        'show.uk.modal': function(){
        },

        'hide.uk.modal': function(){
          modal.getElementsByClassName('uk-modal-content')[0].innerHTML = '';
          modal.getElementsByClassName('uk-modal-spinner')[0].style.display = 'block';
        }
    });
  }

  TodoEdit.prototype.close = function(e) {
    let url = e.target.getAttribute("href");
    let ajax = new $todo.$Ajax();
    ajax.send({
      url:url,
      success: function(data) {
        UIkit.modal.alert("Closed");
        let parsedData = JSON.parse(data);
        let _parseData = new $todoNS.$TodoParse({
          data: (parsedData.category)?parsedData.category.todos:parsedData
        });
        _parseData.parse();
      }
    });
  }

  TodoEdit.prototype.delete = function(e) {
    let url = e.target.getAttribute("href");
        let ajax = new $todo.$Ajax();
        ajax.send({
          url:url,
          success: function(data) {
            UIkit.modal.alert("Deleted");
          }
        });
  }

  function _eventProcess(e, context) {
    e.preventDefault();
    if(e.target.classList.contains('todo_edit')) context.edit(e);
    if(e.target.classList.contains('todo_close')) context.close(e);
    if(e.target.classList.contains('todo_delete')) context.delete(e);
    return false;
  }

  $todoNS.TodoEdit = TodoEdit;
}());

/**
 * Ajax
 * *****************************************************************************
 */
(function(){
  $todoNS = window['$todo'] || (window['$todo'] = {});

  let $Ajax = function() {
    let $this = this;
  }

  $Ajax.prototype.send = function(params) {
    $this = this;
    let defaults = {
      onSend  : function() {},
      success : function() {},
      error   : function() {},
      type    : "POST",
      url     : null,
      data    : null
    };
    $this.cfg = Object.assign(defaults, params);
    var xhr = new XMLHttpRequest();
    xhr.open($this.cfg.type, $this.cfg.url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    if($this.cfg.data) {
      xhr.send($this.cfg.data);
    } else {
      xhr.send();
    }
    $this.cfg.onSend();
    xhr.onload = function (e) {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          $this.cfg.success(xhr.responseText);
        } else {
          $this.cfg.error(xhr.status + ': ' + xhr.statusText);
        }
      }
    };
  }

  $todoNS.$Ajax = $Ajax;

}());


/**
 * TodoParse
 * *****************************************************************************
 */
(function(){
  $todoNS = window['$todo'] || (window['$todo'] = {});

  let $TodoParse = function(param) {
    let $this = this;
    let defaults = {
      data: null
    };
    $this.cfg = Object.assign(defaults, param);
  }

  $TodoParse.prototype.errors = function(form) {
    let $this = this;
    let errors = form.getElementsByClassName('error');
    for(let i =0;i<errors.length;i++) {
      errors[i].classList.remove('error');
    }
    let errorMsg = Object.keys($this.cfg.data);
    for(let i=0;i<errorMsg.length;i++) {
      form.getElementsByClassName(errorMsg[i])[0].classList.add('error');
    }
  }

  $TodoParse.prototype.parse = function() {
    let $this = this;
    let todosList = $todoNS.selectors.todolist;
    todosList.innerHTML = "";
    for(let i=0;i<$this.cfg.data.length;i++) {
      let item = document.createElement("div");
      item.classList.add('uk-block');
      item.classList.add('uk-block-default');
      item.setAttribute('data-id', $this.cfg.data[i].id);
      item.innerHTML = "<h2>" + $this.cfg.data[i].title + "</h2>"+
      "<p>"+$this.cfg.data[i].content+"</p>" +
      new Date($this.cfg.data[i].dateSheduled.timestamp*1000) +
      "<div class='uk-button-group'>" +
      "<a class='uk-button uk-button-small uk-button-primary todo_edit' data-uk-modal=\"{target:'#js-edit-modal', center:true}\" href='/todos/"+$this.cfg.data[i].id+"/edit'>"+$todoNS.translate.action_buttons.todo_edit+"</a>" +
      "<a class='uk-button uk-button-small todo_close' href='/todos/"+$this.cfg.data[i].id+"/close'>"+$todoNS.translate.action_buttons.todo_close+"</a>" +
      "<a class='uk-button uk-button-small uk-button-danger todo_delete' href='/todos/"+$this.cfg.data[i].id+"/delete'>"+$todoNS.translate.action_buttons.todo_delete+"</a>" +
      "</div>";
      todosList.appendChild(item);
    }
    $todoNS.selectors.leftmenu.getElementsByClassName('todos_count')
  }

  $todoNS.$TodoParse = $TodoParse;
}());
