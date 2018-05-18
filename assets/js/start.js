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
      var xhr = new XMLHttpRequest();
      xhr.open('POST', e.target.getAttribute("href"), true);
      // xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.send();
      xhr.onload = function (e) {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            let data = JSON.parse(xhr.responseText);
            todosParse(data);
            let allLi = trigger.closest("ul").getElementsByTagName('a');
            for(let i=0;i<allLi.length;i++) {
              allLi[i].classList.remove('uk-text-success');
            }
            trigger.classList.add("uk-text-success");
          } else {
            console.log(xhr.status + ': ' + xhr.statusText);
          }
        }
      };
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
    this.ajax({
      url:url,
      success: function(data) {
        console.log(data);
      }
    })
  }

  TodoEdit.prototype.close = function() {
    console.log("close");
  }

  TodoEdit.prototype.delete = function() {
    console.log("delete");
  }

  TodoEdit.prototype.ajax = function(opt) {
    let $this = this;
    let def = {
      success: function(){},
      error: function() {},
      type: "POST"
    }
    let cfg = Object.assign(def, opt);
    var xhr = new XMLHttpRequest();
    xhr.open(cfg.type, opt.url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.send();
    xhr.onload = function (e) {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          cfg.success(xhr.responseText);
        } else {
          cfg.error(xhr.status + ': ' + xhr.statusText);
        }
      }
    };
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
