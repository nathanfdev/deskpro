import Widget from './Widget';

class TemplateWidget extends Widget {
  constructor(cm, pos, code, text) {
    super(cm, pos);
    this.code = code;
    const element = document.createElement('span');
    element.innerHTML = text.replace(/^SendmailBundle:/, '');
    element.className = 'twig-include';
    element.onclick = this.handleClick;
    this.setMark(element);
  }

  handleClick = () => {
    console.log(this);
  };
}
export default TemplateWidget;
