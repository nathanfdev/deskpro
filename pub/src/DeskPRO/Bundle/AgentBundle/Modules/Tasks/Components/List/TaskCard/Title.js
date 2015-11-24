import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { TitleForm } from './TitleForm';
import classNames from 'classnames';

export class Title extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    isDone: PropTypes.bool,
    onChange: PropTypes.func,
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      editing: false
    };
  }

  onEdit = () => {
    this.setState({
      editing: true
    });

    this.props.onSetEditing(true);
  };

  onCloseEdit = event => {
    event.preventDefault();
    this.refs.form.onSubmit(event);
  };

  onChange = value => {
    const { onChange, onSetEditing } = this.props;
    this.setState({
      editing: false
    });

    onChange(value);
    onSetEditing(false);
  };

  renderHeader() {
    return (
      <h1 onDoubleClick={this.onEdit}>
        {this.props.value}
      </h1>
    );
  }

  renderForm() {
    return (
      <ClickOut onClickOut={this.onCloseEdit}>
        <TitleForm {...this.props} ref="form" onChange={this.onChange} />
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className={classNames(
          'dpwd--card-title',
          {'strikethrough': this.props.isDone && !this.state.editing}
        )}>

          {this.state.editing ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
