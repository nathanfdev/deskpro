class Widget {
  constructor(cm, pos) {
    this.cm = cm;
    this.pos = pos;
  }

  setMark = (domNode) => {
    const doc = this.cm.getDoc();
    this.mark = doc.markText({
      line: this.pos.line,
      ch:   this.pos.ch
    },
      {
        line: this.pos.line,
        ch:   this.pos.ch + this.code.length
      },
      {
        atomic:       true,
        replacedWith: domNode
      }
    );
  };

  range = () => this.mark.find();

  setText = (text) => {
    const r = this.range();
    this.cm.replaceRange(text, r.from, r.to);
  };

  getText = () => {
    const r = this.range();
    return this.cm.getRange(r.from, r.to);
  }
}
export default Widget;
