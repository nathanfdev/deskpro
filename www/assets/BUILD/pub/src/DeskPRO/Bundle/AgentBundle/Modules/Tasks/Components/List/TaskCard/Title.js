import PropTypes from 'prop-types';
import React from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TitleForm } from './TitleForm';
import classNames from 'classnames';
import { CardWidget } from './CardWidget';

export class Title extends CardWidget {

  static propTypes = {
    value:    PropTypes.string,
    isDone:   PropTypes.bool,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: false,
      value:  props.value,
      isDone: props.isDone
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      isOpen: false,
      value:  props.value,
      isDone: props.isDone
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen !== state.isOpen || this.state.value !== state.value || this.state.isDone !== state.isDone;
  }

  onSubmit = () => {
    this.onClose();
    this.props.onSubmit && this.props.onSubmit(this.state.value);
  };

  renderHeader() {
    return (
      <h1 onDoubleClick={this.onOpen}>
        {this.state.value}
      </h1>
    );
  }

  renderForm() {
    return (
      <ClickOut onClickOut={this.onClose}>
        <TitleForm value={this.state.value} onChange={this.onChange} onSubmit={this.onSubmit} />
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className={classNames(
          'dpwd--card-title',
          { 'strikethrough': this.props.isDone && !this.state.isOpen }
        )}
        >

          {this.state.isOpen ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
