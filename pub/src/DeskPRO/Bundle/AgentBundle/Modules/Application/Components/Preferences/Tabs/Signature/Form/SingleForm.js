import React, { PropTypes } from 'react';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import Immutable from 'immutable';

export class SingleForm extends React.Component {

  static propTypes = {
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

  onSubmit = () => {
    const settings = this.props.settings;
    if (settings && settings.get('signature')) {
      PersonSetting.sendPut();
    }
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

            <input type="submit" value="Save Signature" />
          </form>
        </div>
      </section>
    );
  }
}
