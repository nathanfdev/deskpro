import React, { PropTypes } from 'react';
import { render } from 'react-dom';
import classNames from 'classnames';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Widget from './Widget';
import CodeMirror from '../CodeMirror';

class TemplatePopup extends React.Component {
  static propTypes = {
    text:             PropTypes.string,
    loadTemplate:     PropTypes.func,
    resetTemplate:    PropTypes.func,
    setCurrentWidget: PropTypes.func,
    setValue:         PropTypes.func,
  };
  static defaultProps = {
    loadTemplate() {},
    setValue() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      content:       '',
      currentWidget: '',
      resetSubmit:   false
    };
  }

  componentWillUnmount() {
    if (this.state.currentWidget) {
      this.state.currentWidget.closePopup();
    }
  }

  // Local function to deal with widget inception
  setCurrentWidget = (popup) => {
    if (this.state.currentWidget && this.state.currentWidget !== popup) {
      this.state.currentWidget.closePopup();
    }
    this.setState({
      currentWidget: popup
    });
  };

  getPopUp = () => (
    <div className="template-popup">
      <CodeMirror
        value={this.state.content}
        onChange={this.handleChange}
        ref={(c) => { this.widgetEditor = c; }}
      />
      <div className="footer">
        <Button onClick={this.saveChanges}>Submit</Button>
        <Button className="basic" onClick={this.closePopup}>Cancel</Button>
        <Button
          className={classNames('right floated basic small', { loading: this.state.resetSubmit })}
          disabled={this.state.resetSubmit}
          onClick={this.resetTemplate}
          confirm
        >
          Reset template
        </Button>
      </div>
    </div>
    );

  handleChange = (cm, change) => {
    this.props.addMarks(cm, change, this.setCurrentWidget);
    this.setState({
      content: cm.getValue()
    });
  };

  loadContent = () => {
    this.props.loadTemplate(this.props.text).then(
      (content) => {
        this.setState({
          content,
        });
      }
    );
  };

  openPopup = () => {
    this.loadContent();
    this.popup.openPopup();
    setTimeout(() => {
      this.props.setCurrentWidget(this, this.widgetEditor);
    }, 100);
  };

  closePopup = () => {
    if (this.popup) {
      this.popup.closePopup();
    }
    if (this.state.currentWidget) {
      this.state.currentWidget.closePopup();
    }
  };

  resetTemplate = () => {
    this.setState({
      resetSubmit: true
    });
    this.props.resetTemplate(this.props.text).then(
      (payload) => {
        const content = payload.template_code.code;
        this.props.setValue(this.props.text, content);
        this.setState({
          resetSubmit: false,
          content
        });
      }
    );
  };

  saveChanges = () => {
    this.props.setValue(this.props.text, this.state.content);
    this.popup.closePopup();
    this.props.setCurrentWidget(null, null);
  };

  render() {
    let positionMy = 'left top-1px';
    let positionAt = 'left bottom';
    if (this.span) {
      const viewportOffset = this.span.getBoundingClientRect();
      if (window.innerHeight - viewportOffset.bottom < 370) {
        positionMy = 'left bottom+1px';
        positionAt = 'left top';
      }
    }
    return (
      <PopUp
        positionMy={positionMy}
        positionAt={positionAt}
        zIndex={100}
        content={this.getPopUp()}
        ref={(c) => { this.popup = c; }}
        style={{ display: 'inline-block' }}
        clickOut={false}
        manual
      >
        <span
          onClick={this.openPopup}
          ref={(c) => { this.span = c; }}
        >
          {this.props.text.replace(/^SendmailBundle:/, '')}
        </span>
      </PopUp>
    );
  }
}
class TemplateWidget extends Widget {
  constructor(cm, pos, code, text, setCurrentWidget, loadTemplate, resetTemplate, setValue) {
    super(cm, pos);
    try {
      const element = document.createElement('span');
      element.className = 'twig-include';
      this.setMark(element, code);

      this.text = text;
      this.addMarks = addMarks;
      this.loadTemplate = loadTemplate;
      this.resetTemplate = resetTemplate;
      this.setCurrentWidget = setCurrentWidget;
      this.setValue = setValue;

      this.addReactComponent(element);
    } catch (e) {
      console.error(e);
    }
  }

  addReactComponent = (element) => {
    render(
      <TemplatePopup
        text={this.text}
        addMarks={this.addMarks}
        loadTemplate={this.loadTemplate}
        resetTemplate={this.resetTemplate}
        setCurrentWidget={this.setCurrentWidget}
        setValue={this.setValue}
      />,
      element
    );
  };
}
export default TemplateWidget;
