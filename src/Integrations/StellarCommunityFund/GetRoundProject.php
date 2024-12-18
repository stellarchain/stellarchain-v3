<?php

namespace App\Integrations\StellarCommunityFund;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetRoundProject extends Request
{
    protected Method $method = Method::GET;
    protected int $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId= $projectId;
    }

    public function resolveEndpoint(): string
    {
        return '/suggestion/'.$this->projectId;
    }

    protected function defaultQuery(): array
    {
        return [
            'include' => 'additionalContent:include(formElement|formElementOption|taskName):order(formElement.formElementToForm.sortOrder:asc):filter(deleted:false|formElement.isVisibleInFrontend:true),media:filter(deleted:false):order(created:asc),tags:filter(deleted:false),isLikedByCurrentUser,isFundedByCurrentUser,isRatedByCurrentUser,isFollowedByCurrentUser,team:include(ideaShares|isTeamLead|role|bonus|credit|displayName):filter(deleted:false),team.userNested:include(displayName|image),author:include(displayName|image),canCurrentUserFund,isEditableByCurrentUser,phase:include(startDate|endDate|project.configuration|project.status|project.statusValue|project.isManageableByCurrentUser|project.currentUserProjectRelation.role|project.currentUserProjectRelation.canSeeExpertEvaluation|project.relativeUrl|primaryStep.configuration|totalInvestmentOfCurrentUser|numberOfAvailableVotesForCurrentUser|project.relatedIdeaSetting.phase.project.projectType.type|project.relatedIdeaSetting.phase.project.relativeUrl|project.relatedIdeaSetting.phase.relativeUrl|relativeUrl),suggestionFundingTarget,numberOfVotesByCurrentUser,suggestionProjectRelation,totalBonus,status,allowSuggestionUserApplication,suggestionUserRequirementText,isSuggestionUserApplicationOpen,suggestionUserApplicationsByCurrentUser,suggestionUserApplications:include(user.firstname|user.lastname|user.displayName|user.image):filter(accepted:is(0|-1)):order(accepted:desc),bookmarkOfCurrentUser,investmentOfCurrentUser,suggestionAssociations:filter(cloned:is(0)),suggestionAssociations.suggestionAssociated:include(phase.project.configuration|relativeUrl),cloneParent:include(project|media|relativeUrl),url,relativeUrl,suggestionStatusLabel',
            'language' => 'en',
        ];
    }
}
