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
  }

  function onLeftMenuClick(e) {
    let trigger = e.target;
    e.preventDefault();
    if(e.target.nodeName === "A") {
      let ajax = new $todo.$Ajax();
      ajax.send({
        url: e.target.getAttribute("href"),
        success: function(data) {
          let _data = JSON.parse(data);
          todosParse(_data);
          let allLi = trigger.closest("ul").getElementsByTagName('a');
          for(let i=0;i<allLi.length;i++) {
            allLi[i].classList.remove('uk-text-success');
          }
          trigger.classList.add("uk-text-success");
        }
      });
    }
    return false;
  }

  function todosParse(data)
  {
    let todosList = $todoNS.selectors.todolist;
    todosList.innerHTML = "";
    for(let i=0;i<data.length;i++) {
      let li = document.createElement("div");
      li.classList.add('uk-block');
      li.classList.add('uk-block-default');
      li.innerHTML = "<h2>" +
      data[i].title + "</h2>"+
      "<p>"+data[i].content+"</p>" +
      new Date(data[i].dateSheduled.timestamp*1000) +
      "<div class='uk-button-group'> <a class='uk-button uk-button-small uk-button-danger' href=''>delete</a> <a class='uk-button uk-button-small uk-button-primary' href=''>close</a> </div>";
      todosList.appendChild(li);
    }
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
            url: url,
            data:formData,
            success: function() {
              UIkit.modal.alert("Saved!");
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
    this.ajax({
      url:url,
      success: function(data) {
        UIkit.modal.alert("Closed");
      },
      error: function(data) {
        modal.getElementsByClassName('uk-modal-spinner')[0].style.display = 'none';
      }
    });
  }

  TodoEdit.prototype.delete = function() {
    console.log("delete");
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
