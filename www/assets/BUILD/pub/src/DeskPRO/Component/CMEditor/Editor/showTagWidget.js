import PropTypes from 'prop-types';
import React from 'react';
import { render } from 'react-dom';
import { Button } from '@deskpro/react-components';
import { PopUp } from '../../Semantic/PopUp';
import Widget from './Widget';
import { Field, Input } from '../../Semantic/Form';
import { TemplatePopup } from './TemplateWidget';

class ShowTagPopup extends React.Component {
  static propTypes = {
    text:             PropTypes.string,
    loadTagInfo:      PropTypes.func,
    loadTemplate:     PropTypes.func,
    resetTemplate:    PropTypes.func,
    setCurrentWidget: PropTypes.func,
    setValue:         PropTypes.func,
    addMarks:         PropTypes.func,
    variables:        PropTypes.object,
  };
  static defaultProps = {
    loadTemplate() {},
    setValue() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      tagInfo:       {},
      currentWidget: '',
      variables:     {},
      resetSubmit:   false
    };
  }

  componentWillMount() {
    this.setState({
      variables: this.props.variables
    });
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
    <div className="show-tag-popup">
      {this.getTemplate()}
      {this.getVariables()}
      <div className="footer">
        <Button type="primary" onClick={this.saveChanges}>Submit</Button>
        <Button type="secondary" onClick={this.closePopup}>Cancel</Button>
      </div>
    </div>
    );

  getTemplate = () => {
    if (!this.state.tagInfo.template) {
      return null;
    }
    return (
      <div className="template">
        <div className="ui horizontal divider">Template</div>
        <span className="twig-include">
          <TemplatePopup
            text={this.state.tagInfo.template}
            addMarks={this.props.addMarks}
            loadTemplate={this.props.loadTemplate}
            resetTemplate={this.props.resetTemplate}
            setCurrentWidget={this.setCurrentWidget}
            setValue={this.props.setValue}
          />
        </span>
      </div>
    );
  };

  getVariables = () => {
    const { variables } = this.state;
    if (Object.keys(variables).length === 0) {
      return null;
    }
    this.variableInputs = [];
    return (
      <div className="variables">
        <div className="ui horizontal divider">Variables</div>
        {Object.keys(variables).map(key =>
          <Field key={`variable_${key}`} field={`variable_${key}`} errors={null}>
            <label htmlFor={`variable_${key}`}>{key}</label>
            <Input
              id={`variable_${key}`}
              name={`variable_${key}`}
              defaultValue={variables[key]}
              ref={(c) => { this.variableInputs.push(c); }}
            />
          </Field>
        )}
      </div>
    );
  };

  handleChange = (cm, change) => {
    this.props.addMarks(cm, change, this.setCurrentWidget);
    this.setState({
      content: cm.getValue()
    });
  };

  loadInfo = () => {
    this.props.loadTagInfo(this.props.text).then(
      (tagInfo) => {
        this.setState({
          tagInfo,
        });
      }
    );
  };

  openPopup = () => {
    this.loadInfo();
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
      if (window.innerHeight - viewportOffset.bottom < 400) {
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
class ShowTagWidget extends Widget {
  constructor(
    cm,
    pos,
    code,
    text,
    matches,
    setCurrentWidget,
    loadTemplate,
    resetTemplate,
    setValue,
    loadTagInfo,
    addMarks
  ) {
    super(cm, pos);
    try {
      const element = document.createElement('span');
      element.className = 'twig-include';
      this.setMark(element, code);

      this.text = text;
      this.matches = matches;
      this.addMarks = addMarks;
      this.loadTagInfo = loadTagInfo;
      this.loadTemplate = loadTemplate;
      this.resetTemplate = resetTemplate;
      this.setCurrentWidget = setCurrentWidget;
      this.setValue = setValue;

      this.addReactComponent(element, matches);
    } catch (e) {
      console.error(e);
    }
  }

  addReactComponent = (element) => {
    const { matches } = this;
    const variables = {};
    if (matches[4]) {
      matches[4].trim().split(',').forEach((variable) => {
        const pieces = variable.split(':');
        variables[pieces[0].trim()] = pieces[1].trim();
      });
    }
    render(
      <ShowTagPopup
        text={this.text}
        variables={variables}
        addMarks={this.addMarks}
        loadTemplate={this.loadTemplate}
        loadTagInfo={this.loadTagInfo}
        resetTemplate={this.resetTemplate}
        setCurrentWidget={this.setCurrentWidget}
        setValue={this.setValue}
      />,
      element
    );
  };
}
export default ShowTagWidget;
