import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TitleForm } from './TitleForm';
import classNames from 'classnames';
import { CardWidget } from './CardWidget';

export class Title extends CardWidget {

  static propTypes = {
    value:    PropTypes.string,
    isDone:   PropTypes.bool,
    onSubmit: PropTypes.func
  };

  onSubmit = value => {
    this.onClose();
    this.props.onSubmit(value);
    this.setState({ value });
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
        <TitleForm value={this.state.value} onSubmit={this.onSubmit} />
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className={classNames('dpwd--card-title', { strikethrough: this.props.isDone && !this.state.isOpen })}>
          {this.state.isOpen ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
