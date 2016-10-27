import React from 'react';
import { Field, Toggle, BlurInput } from 'DeskPRO/Component/Semantic/ReactForm';

class CallForward extends React.Component {

  render() {
    return (
      <div className="call-forward">
        <Field select="enable_forward">
          <Toggle className="small">
            Enable call forwarding
          </Toggle>
        </Field>
        <Field select="number" label="Forwarding number">
          <BlurInput type="text" />
        </Field>
      </div>
    );
  }
}

export default CallForward;
