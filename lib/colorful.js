/*
 * テキストフィールドのフォーカス時に背景色を変更する JavaScript
 * http://espion.just-size.jp/archives/05/231211111.html
 * より
 *
 * 2007/05/15
 *
 */

var colorful = new ColorfulInput;
colorful.skip = ['submit'];
colorful.color['focus'] = '#F6F6CC';

window.onload = function() {
   colorful.set();
}

function ColorfulInput() {
   this.skip  = [];
   this.color = { 'blur': '', 'focus': '#EEEEEE' };

   this.set = function() {
      for (var i = 0; i < document.forms.length; i++) {
         for (var f = 0; f < document.forms[i].length; f++) {
            var elm = document.forms[i][f];
            if(!this._checkSkip(elm)) continue;

            this._setColor(elm, 'focus');
            this._setColor(elm, 'blur');
         }
      }
   }

   this._checkSkip = function(elm) {
      for(var i in this.skip) {
         if(elm.type == this.skip[i]) return false;
      }
      return true;
   }

   this._setColor = function(elm, type) {
      var color = this.color[type];
      var event = function() { elm.style.backgroundColor = color; };

      if(elm.addEventListener) {
         elm.addEventListener(type, event, false);
      } else if(elm.attachEvent) {
         elm.attachEvent('on'+type, event);
      } else {
         elm['on'+type] = event;
      }
   }
}
