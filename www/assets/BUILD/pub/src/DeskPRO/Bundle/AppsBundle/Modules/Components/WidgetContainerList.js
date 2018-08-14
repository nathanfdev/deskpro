import '@deskpro/apps-components-style'; // eslint-disable-line import/extensions

import PropTypes from 'prop-types';
import React from 'react';

import { WidgetContainer } from './WidgetContainer';
import {  WidgetConfiguration } from '../Domain';

export class WidgetContainerList extends React.PureComponent {
  static propTypes = {
    widgets:           PropTypes.arrayOf(WidgetConfiguration).isRequired,
    getEvent:          PropTypes.func.isRequired,
    getEventProviders: PropTypes.func.isRequired,
    unregister:        PropTypes.func.isRequired,
  };

  renderWidget = configuration =>  (<WidgetContainer
    configuration={configuration}
    getEvent={this.props.getEvent}
    getEventProviders={this.props.getEventProviders}
    unregister={this.props.unregister}
  />);

  render()  {
    return this.props.widgets.map(this.renderWidget);
  }
}
