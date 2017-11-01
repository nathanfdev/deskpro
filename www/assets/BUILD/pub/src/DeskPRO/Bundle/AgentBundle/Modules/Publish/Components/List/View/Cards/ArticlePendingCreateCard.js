import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import {
  Card,
  CardLine,
  CardLineLeft,
  CardLineRight,
  CardLineFull,
  CardContentText,
  CardLineItem,
  CardCheckbox,
  CardDisc,
  CardUser
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class ArticlePendingCreateCard extends Component {

  static propTypes = {
    intl:           intlShape.isRequired,
    element:        PropTypes.object.isRequired,
    author:         PropTypes.object.isRequired,
    assigned:       PropTypes.object.isRequired,
    selected:       PropTypes.bool.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  render = () => {
    const { element, author, toggleSelected, selected, assigned } = this.props;

    return (
      <Card type="article">

        <CardCheckbox selected={selected} onClick={toggleSelected} />

        <CardLine>
          <CardLineRight>
            <CardUser user={author} />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p><SlicedString string={element.get('comment')} length={255} /></p>
            </CardContentText>
          </CardLineFull>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              <CardUser user={assigned} />
              <CardDisc />
              <FormattedRelative value={element.get('date_created')} />
              <CardDisc />
            </CardLineItem>
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }

}
