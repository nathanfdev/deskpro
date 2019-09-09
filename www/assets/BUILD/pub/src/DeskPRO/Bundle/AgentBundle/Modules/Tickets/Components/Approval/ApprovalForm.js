import React from "react";
import PropTypes from "prop-types";
import {Button} from "@deskpro/react-components";
import {FormattedMessage} from "react-intl";

export class ApprovalForm extends React.Component {
  static propTypes = {
    people:      PropTypes.object.isRequired,
    ticketPerms: PropTypes.object
  };

  constructor(props) {
    super(props);

    const { ticketPerms } = this.props;

    this.state = {
      templates: [],
      approvers: [],
      errors:    [],
      saving:    false,
    };
  }

  createApprovalRequest = () => {
    if (this.state.saving) {
      return false;
    }
    this.setState({
      saving: true
    });

    let errors = [];
  };

  renderErrors = () => {
    if (this.state.errors.length === 0) {
      return null;
    }
    return (
      <div className="errors">
        <ul>
          {this.state.errors.map((error, index) => <li key={index}>{error}</li>)}
        </ul>
      </div>
    );
  };

  render() {
    return (
      <div>


        <Button
          size="medium"
          onClick={this.createApprovalRequest}
          loading={this.state.saving}
        >
          <FormattedMessage id="agent.general.create" />
        </Button>
        {this.renderErrors()}
      </div>
    );
  }
}
export default ApprovalForm;
