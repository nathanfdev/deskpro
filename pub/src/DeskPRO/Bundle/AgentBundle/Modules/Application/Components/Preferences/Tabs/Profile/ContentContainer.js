import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import * as LanguagesActions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';
import { languagesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Selectors/languagesSelectors';

@connect(state => ({
  languages: languagesSelector(state)
}))
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    props.dispatch(LanguagesActions.loadAll());
  }

  render() {
    const { dispatch, languages } = this.props;

    return (
      <div>
        <Content dispatch={dispatch} languages={languages} />
      </div>
    );
  }
}
