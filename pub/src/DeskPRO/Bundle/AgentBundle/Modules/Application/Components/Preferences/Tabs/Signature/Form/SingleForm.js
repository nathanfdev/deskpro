import React, { PropTypes } from 'react';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import * as SettingsActions from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/settingsActions';
import Immutable from 'immutable';

export class SingleForm extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    settings: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    const settings = this.props.settings;
    const signature = settings && settings.get('signature') || Immutable.fromJS({});

    this.state = {
      signature: signature.get('value')
    };
  }

  onChange = event => {
    this.setState({
      signature: event.target.value
    });
  };

  onSubmit = event => {
    event.preventDefault();

    const { settings, dispatch } = this.props;
    let promise;

    if (settings && settings.get('signature')) {
      promise = PersonSetting.put('signature', this.state.signature);
    } else {
      promise = PersonSetting.post('signature', this.state.signature);
    }

    promise
      .success(response => {
        const records = {};
        records[response.data.id] = response.data;

        dispatch(SettingsActions.releaseSettings('my'));
        dispatch(SettingsActions.setSettingsRequest('my', records, [response.data.id]));
      });
  };

  render() {
    return (
      <section className="single-signature" id="single-signature">
        <p>This signature will be appended automatically when you send ticket replies. Any reply drafts will be cleared when updating your signature.</p>

        <div className="signature">
          <form>
            <div className="textarea-tagalong"></div>
              <textarea placeholder="Your Signature"
                        value={this.state.signature}
                        onChange={this.onChange} />

            <input type="submit" value="Save Signature" onClick={this.onSubmit} />
          </form>
        </div>
      </section>
    );
  }
}
