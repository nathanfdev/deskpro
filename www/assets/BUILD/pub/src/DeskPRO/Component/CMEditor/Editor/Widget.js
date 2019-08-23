import CM from 'codemirror';
import { unmountComponentAtNode } from 'react-dom';

class Widget {
  constructor(cm, pos) {
    this.cm = cm;
    this.pos = pos;
  }

  setMark = (domNode, code) => {
    this.domNode = domNode;
    const doc = this.cm.getDoc();
    this.mark = doc.markText({
      line: this.pos.line,
      ch:   this.pos.ch
    },
      {
        line: this.pos.line,
        ch:   this.pos.ch + code.length
      },
      {
        atomic:       true,
        replacedWith: this.domNode
      }
    );
    CM.on(this.mark, 'hide', () => {
      unmountComponentAtNode(this.domNode);
    });
    CM.on(this.mark, 'unhide', () => {
      this.addReactComponent(this.domNode);
    });
  };

  unmount = () => {
    unmountComponentAtNode(this.domNode);
  };

  range = () => this.mark.find();

  setText = (text, origin) => {
    const r = this.range();
    this.cm.replaceRange(text, r.from, r.to, origin);
  };

  getText = () => {
    const r = this.range();
    return this.cm.getRange(r.from, r.to);
  };

  addReactComponent = () => {}
}
export default Widget;
