import React, { Component } from 'react';
import { ContentTab } from './ContentTab';
import { ButtonsPane, Button } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';

export class KBTab extends Component {
  static propTypes = ContentTab.propTypes;

  render() {
    return (
      <div>
        <ContentTab content="articles" count={this.props.count} />

        <ButtonsPane>
          <Button title="Glossary" icon="fa-quote-left" />
          <Button title="Search" icon="fa-search" />
          <Button title="Comments" icon="fa-comments-o" />
        </ButtonsPane>
      </div>
    );
  }
}
