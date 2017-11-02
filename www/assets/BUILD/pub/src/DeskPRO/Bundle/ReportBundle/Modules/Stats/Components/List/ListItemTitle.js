import React, { PropTypes } from 'react';
import { Select } from 'DeskPRO/Component/Semantic/ReactForm';

class ListItemTitle extends React.Component {

  static propTypes = {
    report:            PropTypes.object.isRequired,
    groupParams:       PropTypes.object.isRequired,
    onChangeReportVar: PropTypes.func.isRequired,
    onRunClick:        PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      title: props.report.get('title'),
      vars:  props.report.get('variables').map(value => value.set('value', value.get('default', 'none'))).toJS()
    };
    this.dateChoices = this.props.groupParams.get('dates').map((date, index) => { const choice = { value: index, label: date.get(0) }; return choice; }).toList().toJS();
  }

  onChange(varName, value) {
    const { vars } = this.state;
    const { report, onChangeReportVar } = this.props;
    vars[varName] = value;
    this.setState({ vars });
    let newReport = report;

    newReport.get('variables').forEach((val, index) => {
      if (val.get('name') === varName) {
        newReport = newReport.setIn(['variables', index, 'value'], value);
      }
    });
    onChangeReportVar(newReport);
  }

  replaceVars() {
    let title = this.props.report.get('title');
    title = title.replace(/\$\{([a-zA-Z0-9_]+)\}/g, '#VAR#$$$$$1#VAR#');
    title = title.split('#VAR#');

    return title.map((value, index) => {
      if (value.indexOf('$$') === 0) {
        const varName = value.substr(2);
        return this.replaceVarWithSelectBox(varName);
      }
      return <span key={`title_${index}`} onClick={this.props.onRunClick}>{value}</span>;
    });
  }

  replaceVarWithSelectBox(varName) {
    const entry = this.props.report.get('variables').findEntry(value => value.get('name') === varName);
    if (entry) {
      switch (entry[1].get('type')) {
        case 'dates':
          return this.renderDatesSelectBox(entry);
        case 'fields':
        case 'statuses':
        case 'orders':
          return this.renderGroupSelectBox(entry);
        default:
          return varName;
      }
    }
    return varName;
  }

  renderDatesSelectBox(entry) {
    const varName = entry[1].get('name');
    return (<Select
      key={`dates_${entry[0]}`}
      clearable={false}
      value={this.state.vars[varName]}
      choices={this.dateChoices}
      onChange={value => this.onChange(varName, value)}
    />);
  }

  renderGroupSelectBox(entry) {
    const varName = entry[1].get('name');

    const choices = this.props.groupParams.getIn([entry[1].get('type'), entry[1].get('field_type')], [])
      .map((value, key) => { const choice = { label: value.get(0), value: key }; return choice; })
      .toList()
      .toJS();

    return (<Select
      key={`${entry[1].get('type')}_${entry[0]}`}
      clearable={false}
      value={this.state.vars[varName]}
      choices={choices}
      onChange={value => this.onChange(varName, value)}
    />);
  }

  render() {
    const title = this.replaceVars();

    return <span><a>{ title }</a></span>;
  }
}

export default ListItemTitle;
