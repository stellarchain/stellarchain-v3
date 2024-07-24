<?php
namespace App\Integrations\StellarCommunityFund;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetRoundData extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/project';
    }

    protected function defaultQuery(): array
    {
        return [
            'include' => 'configuration,terms,statusValue,startDate,endDate,about,projectType:include(name|type|templateName|isProjectPeriodAutoUpdated|allowProjectAssignment|canSubmitDrafts|workflow),userFromProjectGroup:name(Contact):include(userNested.email|userNested.firstname|userNested.lastname|userNested.image|userNested.displayName|userNested.badges),image,imageId,teaser,teaserCategory,teaserId,primaryActivePhase:include(startDate|endDate|primaryStep|relativeUrl):order(startDate:desc|endDate:desc):filter(deleted:false),activePhases:include(startDate|endDate|primaryStep|relativeUrl):order(startDate:desc|endDate:desc):filter(deleted:false),activePhases.primaryStep.competenceView,activePhases.primaryStep.configuration,futurePhases:include(startDate|endDate|primaryStep|relativeUrl):order(startDate:desc|endDate:desc):filter(deleted:false),pastPhases:include(startDate|endDate|primaryStep|relativeUrl):order(startDate:desc|endDate:desc):filter(deleted:false),pastPhases.primaryStep.competenceView,startDate,overviewDescription,endDate,isManageableByCurrentUser,isFollowedByCurrentUser,currentUserProjectRelation:include(role|acceptedTos),numberOfSuggestions,numberOfUsers,numberOfComments,totalFunding,numberOfPublishedBlogArticles,statusId,statusCaption,parentProjectId,parentProject.configuration,isCurrentUserAuthorOfProject,currentUserHasSolutionScoutingEditRights,basedOnSuggestion.author,bookmarkOfCurrentUser,showTotalBonusOfSuggestions,suggestions:filter(totalRatings:true),relatedIdeaSetting,url,teaserLink,relativeUrl,enableFrontendSuggestionExport,allowSuggestionUserApplication,phases:include(primaryStep.configuration|startDate|endDate|relativeUrl):filter(deleted:false,isActive:true),primaryButtonEnabled,primaryButtonLabel,primaryButtonUrl',
            'language' => 'en'
        ];
    }
}
