import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { AgentsList } from './AgentsList';
import classNames from 'classnames';

export class CheckboxListElement extends React.Component {

  static propTypes = {
    renderLabel: PropTypes.func.isRequired,
    option: PropTypes.object,
    isChecked: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      isChecked: props.isChecked
    };
  }

  onClick = () => {
    this.setState({isChecked: !this.state.isChecked});
  };

  render() {
    console.info('render');
    const { renderLabel, option } = this.props;
    return (
      <li>
        <a className={classNames('checkbox-button', {'checked': this.state.checked})} onClick={this.onClick}>
          <span className="checkbox">
            {this.state.isChecked ? <i className="fa fa-check"></i> : null}
          </span>
          {renderLabel(option)}
        </a>
      </li>
    );
  }
}
