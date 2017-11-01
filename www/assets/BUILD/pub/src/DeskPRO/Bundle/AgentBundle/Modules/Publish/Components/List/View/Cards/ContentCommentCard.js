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
  CardTitle,
  CardUser
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class ContentCommentCard extends Component {

  static propTypes = {
    intl:           intlShape.isRequired,
    element:        PropTypes.object.isRequired,
    parent:         PropTypes.object.isRequired,
    author:         PropTypes.object.isRequired,
    selected:       PropTypes.bool.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  render = () => {
    const { element, author, toggleSelected, selected, parent } = this.props;

    return (
      <Card type="article">

        <CardCheckbox selected={selected} onClick={toggleSelected} />

        <CardLine>
          <CardLineLeft>
            <CardTitle content={parent.get('title')} />
          </CardLineLeft>
          <CardLineRight>
            <CardUser user={author} />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p><SlicedString string={element.get('content')} length={255} /></p>
            </CardContentText>
          </CardLineFull>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>{element.get('status')}</CardLineItem>
            <CardLineItem>
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
