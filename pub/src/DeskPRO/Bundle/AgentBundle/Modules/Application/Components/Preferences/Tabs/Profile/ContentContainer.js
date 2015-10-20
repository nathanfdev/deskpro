import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import * as LanguagesActions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';

@connect()
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    props.dispatch(LanguagesActions.loadAll());
  }

  render() {
    return (
      <div>
        <Content dispatch={this.props.dispatch} />
      </div>
    );
  }
}
