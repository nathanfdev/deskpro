import PropTypes from 'prop-types';
import React, { Component } from 'react';

export class ButtonsPane extends Component {
  static propTypes = {
    children: PropTypes.node
  };

  buttonsFromChildren() {
    const buttons  = [];
    const children = this.props.children.length ? this.props.children : [this.props.children];
    for (let i = 0; i < children.length; i++) {
      if (children[i].type.displayName !== 'Button') {
        throw new Error('ButtonsPane can only contain Button components as first level children');
      }

      buttons.push({ index: i, title: children[i].props.title, icon: children[i].props.icon });
    }

    return buttons;
  }

  renderButton = ({ title, icon, index }) => {
    const className = `fa ${icon}`;

    return (
      <li key={index}>
        <a href="#">
          <span className="icon"><i className={className} /></span>
          <span className="title">{title}</span>
        </a>
      </li>
    );
  };

  render() {
    const buttons = this.buttonsFromChildren();

    return (
      <div className="deskpro-app-major-buttons">
        <ul>
          {buttons.map(button => this.renderButton(button))}
        </ul>
      </div>
    );
  }
}

export class Button extends React.Component {
  static displayName = 'Button';

  render() {
    return null;
  }
}
