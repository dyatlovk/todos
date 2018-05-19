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
    this.ajax({
      url:url,
      success: function(data) {
        modal.getElementsByClassName('uk-modal-content')[0].innerHTML = data;
        modal.getElementsByClassName('uk-modal-spinner')[0].style.display = 'none';
        let form = modal.getElementsByTagName('form')[0];
        form.getElementsByClassName('submit')[0].addEventListener('click', function(e){
          e.preventDefault();
          let ajax = new $todoNS.$Ajax();
          let serData = ajax.serialize(form);
          console.log(String(serData));
          ajax.send(serData);
          return false;
        })
      },
      error: function(data) {
        modal.getElementsByClassName('uk-modal-content')[0].innerHTML = data;
        modal.getElementsByClassName('uk-modal-spinner')[0].style.display = 'none';
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

  TodoEdit.prototype.ajax = function(opt) {
    let $this = this;
    let def = {
      onSend  : function() {},
      success : function(){},
      error   : function() {},
      type    : "POST"
    }
    let cfg = Object.assign(def, opt);
    var xhr = new XMLHttpRequest();
    xhr.open(cfg.type, opt.url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.send();
    cfg.onSend();
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

/**
 * Ajax
 * *****************************************************************************
 */
(function(){
  $todoNS = window['$todo'] || (window['$todo'] = {});

  let $Ajax = function(params) {
    let $this = this;
    let defaults = {
      onSend  : function() {},
      success : function(){},
      error   : function() {},
      type    : "POST"
    };
    $this.cfg = Object.assign(defaults, params);
  }

  $Ajax.prototype.serialize = function(form) {
    if (!form || form.nodeName !== "FORM") {
      return;
    }
    var i, j,
      obj = {};
    for (i = form.elements.length - 1; i >= 0; i = i - 1) {
      if (form.elements[i].name === "") {
        continue;
      }
      switch (form.elements[i].nodeName) {
      case 'INPUT':
        switch (form.elements[i].type) {
        case 'text':
        case 'hidden':
        case 'password':
        case 'button':
        case 'reset':
        case 'submit':
          obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
          break;
        case 'checkbox':
        case 'radio':
          if (form.elements[i].checked) {
            obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
          }
          break;
        case 'file':
          break;
        }
        break;
      case 'TEXTAREA':
        obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
        break;
      case 'SELECT':
        switch (form.elements[i].type) {
        case 'select-one':
          obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
          break;
        case 'select-multiple':
          for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
            if (form.elements[i].options[j].selected) {
              obj[form.elements[i].name] = encodeURIComponent(form.elements[i].options[j].value);
            }
          }
          break;
        }
        break;
      case 'BUTTON':
        switch (form.elements[i].type) {
        case 'reset':
        case 'submit':
        case 'button':
          obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
          break;
        }
        break;
      }
    }
    return obj;
  }

  $Ajax.prototype.send = function(url) {
    $this = this;
    var xhr = new XMLHttpRequest();
    xhr.open($this.cfg.type, url, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.send();
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
