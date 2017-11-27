import $ from 'jquery';
import PropTypes from 'prop-types';
import React from 'react';
import onClickOutside from 'react-onclickoutside';
import Portal from 'react-portal/build/portal';

class InlineSelectComp extends React.Component {

  static defaultProps = {
    onChange:    null,
    defaultText: ''
  };

  static propTypes = {
    options: PropTypes.array.isRequired,
    value:   PropTypes.oneOfType([
      PropTypes.string, PropTypes.number, PropTypes.array, PropTypes.object
    ]).isRequired,
    onChange:    PropTypes.func,
    defaultText: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = { isOpen: false };
  }

  componentDidUpdate() {
    this.refreshSize();
  }

  onClick = (ev, value) => {
    ev.stopPropagation();
    if (this.props.onChange) {
      this.props.onChange(value);
      this.setState({ isOpen: false });
    }
  };

  handleClickOutside = () => {
    this.setState({ isOpen: false });
  };

  open = () => {
    this.setState({
      isOpen: true
    });
  };

  refreshSize() {
    if (!this.$el || !this.$menu || !this.state.isOpen) {
      return;
    }

    const winH = $(window).outerHeight();
    const winW = $(window).outerWidth();
    const w    = this.$menu.outerWidth();
    const h    = this.$menu.outerHeight();
    const pos  = this.$el.offset();

    let posLeft = pos.left;
    let posTop  = pos.top + 20;

    if (pos.top + h > winH) {
      posTop = pos.top - h;
    }
    if (pos.left + w > winW) {
      posLeft = pos.left - w;
    }

    if (posTop !== this.state.posTop || posLeft !== this.state.posLeft) {
      this.setState({
        posTop, posLeft
      });
    }
  }

  renderMenu() {
    const options = this.props.options.map(o => (
      <div
        key={`${o.value || o.label}`}
        className="inline-select-menu-item ignore-react-onclickoutside"
        onClick={ev => this.onClick(ev, o.value)}
      >
        {o.label || o.value}
      </div>)
    );

    const style = {
      top:  this.state.posTop || 0,
      left: this.state.posLeft || 0,
    };

    return (
      <div
        ref={(el) => { this.$menu = $(el); }}
        className="inline-select-menu ignore-react-onclickoutside"
        style={style}
      >
        {options}
      </div>
    );
  }

  render() {
    const defaultText = this.props.defaultText || 'Select...';
    const value       = this.props.value || null;

    const valueOpt = this.props.options
      .filter(o => o.value === value || o === value || (value.value && o.value === value.value));

    return (
      <div ref={(el) => { this.$el = $(el); }} className="inline-select">
        <span className="inline-select-label" onClick={this.open}>
          {valueOpt ? (valueOpt[0].label || valueOpt[0].value) : defaultText}
        </span>
        <span className="inline-select-arrow" onClick={this.open}>▼</span>
        <Portal isOpened={this.state.isOpen}>{this.renderMenu()}</Portal>
      </div>
    );
  }
}

const InlineSelect = onClickOutside(InlineSelectComp);

class TitleWithVars extends React.Component {

  static propTypes = {
    report:            PropTypes.object.isRequired,
    groupParams:       PropTypes.object.isRequired,
    onChangeReportVar: PropTypes.func.isRequired,
    onRunClick:        PropTypes.func,
  };

  static defaultProps = {
    onRunClick: () => {},
  };

  static transformVars(report) {
    const vars = {};
    report
      .get('variables')
      .filter(value => value.has('default') && value.get('default') || value.has('value') && value.get('value'))
      .forEach((value) => {
        vars[value.get('name')] = value.has('value') && value.get('value') ? value.get('value') : value.get('default');
      });
    return vars;
  }

  static cancelClick(ev) {
    ev.stopPropagation();
  }

  constructor(props) {
    super(props);
    const vars = TitleWithVars.transformVars(props.report);
    this.state = { vars };
    this.dateChoices = this.props.groupParams
      .get('dates')
      .map((date, index) => { const choice = { value: index, label: date.get(0) }; return choice; })
      .toList()
      .toJS();
  }

  componentWillReceiveProps(props) {
    const vars = TitleWithVars.transformVars(props.report);
    this.setState({ vars });
  }

  onChange(varName, value) {
    const { report, onChangeReportVar } = this.props;
    const { vars } = this.state;
    vars[varName] = value;
    this.setState({ vars });
    let newReport = report;

    newReport.get('variables').forEach((val, index) => {
      if (val.get('name') === varName) {
        newReport = newReport.setIn(['variables', index, 'value'], value);
      }
    });
    onChangeReportVar(report, varName, value);
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
      // eslint-disable-next-line react/no-array-index-key
      return <div key={`title_${index}`} onClick={this.props.onRunClick} className="text">{value}</div>;
    });
  }

  replaceMissingVars() {
    const title = this.props.report.get('title');
    const vars = this.props.report
      .get('variables')
      .filter(value => title.indexOf(`\${${value.get('name')}}`) === -1);

    return vars.map(value => (
      <div key={`${value.get('name')}`}>
        {`\${${value.get('name')}}`}: {this.replaceVarWithSelectBox(value.get('name'))}
      </div>
    ));
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

    const initialValue = this.state.vars[varName] || this.dateChoices[0];

    return (<div onClick={TitleWithVars.cancelClick} key={`dates_${entry[0]}`}><InlineSelect
      value={initialValue}
      options={this.dateChoices}
      onChange={value => this.onChange(varName, value)}
    /></div>);
  }

  renderGroupSelectBox(entry) {
    const varName = entry[1].get('name');

    const choices = this.props.groupParams.getIn([entry[1].get('type'), entry[1].get('field_type')], [])
      .map((value, key) => { const choice = { label: value.get(0), value: key }; return choice; })
      .toList()
      .toJS();

    const initialValue = this.state.vars[varName] || choices[0];

    return (<div onClick={TitleWithVars.cancelClick} key={`${entry[1].get('type')}_${entry[0]}`}><InlineSelect
      value={initialValue}
      options={choices}
      onChange={value => this.onChange(varName, value)}
    /></div>);
  }

  render() {
    const title = this.replaceVars();
    const missingVars = this.replaceMissingVars();

    return (
      <div className="title-with-vars">
        { title }
        { missingVars }
      </div>
    );
  }
}

export default TitleWithVars;
