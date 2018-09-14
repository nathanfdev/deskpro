import '@deskpro/apps-components-style'; // eslint-disable-line import/extensions

import PropTypes from 'prop-types';
import React from 'react';

import { WidgetContainer } from './WidgetContainer';
import {  WidgetConfiguration } from '../Domain';

export class WidgetContainerList extends React.PureComponent {
  static propTypes = {
    isVisible:         PropTypes.bool,
    widgets:           PropTypes.arrayOf(WidgetConfiguration).isRequired,
    getEvent:          PropTypes.func.isRequired,
    getEventProviders: PropTypes.func.isRequired,
    unregister:        PropTypes.func.isRequired,
  };

  static defaultProps = {
    isVisible: true
  };

  constructor(props) {
    super(props);
    this.wrapperRef = React.createRef();
  }

  componentDidUpdate()  {
    const node = this.wrapperRef.current;
    if (this.props.isVisible) {
      node.style.visibility = 'visible';
      node.style.position = 'static';
    } else {
      node.style.visibility = 'hidden';
      node.style.position = 'absolute';
    }
  }

  renderWidget = configuration =>  (<WidgetContainer
    configuration={configuration}
    getEvent={this.props.getEvent}
    getEventProviders={this.props.getEventProviders}
    unregister={this.props.unregister}
  />);

  render()  {
    return (<div ref={this.wrapperRef}>
      { this.props.widgets.map(this.renderWidget) }
    </div>);
  }
}
