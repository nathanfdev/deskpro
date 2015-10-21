import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import * as LanguagesActions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';
import * as TimezonesActions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/timezonesActions';
import { languagesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Selectors/languagesSelectors';
import { timezonesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Selectors/timezonesSelectors';

@connect(state => ({
  languages: languagesSelector(state),
  timezones: timezonesSelector(state)
}))
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    props.dispatch(LanguagesActions.loadAll());
    props.dispatch(TimezonesActions.loadAll());
  }

  render() {
    const { dispatch, languages, timezones } = this.props;

    return (
      <div>
        <Content dispatch={dispatch}
                 languages={languages}
                 timezones={timezones} />
      </div>
    );
  }
}
