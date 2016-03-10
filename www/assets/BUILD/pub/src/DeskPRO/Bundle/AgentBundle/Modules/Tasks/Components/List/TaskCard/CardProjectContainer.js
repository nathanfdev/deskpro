import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { CardProject } from './CardProject';

@connect(state => ({projects: allSelectorFactory('Project')(state)}))

export class CardProjectContainer extends React.Component {

  render() {
    return (
      <CardProject {...this.props} />
    );
  }

}